<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\SampleDraft;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\SampleType;
use Smt\Masterweb\Models\Packet;
use Smt\Masterweb\Models\Program;
use Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Models\SampleMethodDraft;
use Smt\Masterweb\Models\LaboratoriumMethod;
use Smt\Masterweb\Models\LabNum;
use Smt\Masterweb\Models\StartNum;
use Smt\Masterweb\Models\PenerimaanSample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\MethodSampleTypePrice;
use Smt\Masterweb\Models\Unit;
use Smt\Masterweb\Models\Library;
use Smt\Masterweb\Models\JenisMakanan;
use Smt\Masterweb\Models\KesmasSampleNumberSettings;
use Smt\Masterweb\Models\NomerLabKesmas;
use Smt\Masterweb\Models\GlobalLabSequence;
use Smt\Masterweb\Models\GlobalLabSequenceDetail;
use Smt\Masterweb\Helpers\BakuMutuSampletypeHelper;

class LaboratoriumSampleDraftManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new draft sample.
     *
     * @param  string  $id  permohonan_uji_id
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $permohonan_uji = PermohonanUji::findOrFail($id);

        // Get sample types
        $sampletypes = SampleType::orderBy('created_at')->get();

        // Get packets with their sample type from ms_packet table directly
        $packets = Packet::where('id_packet', '!=', '0')
            ->whereNotNull('name_packet')
            ->where('name_packet', '!=', '')
            ->with(['packet_detail.method', 'sampletype'])
            ->orderBy('created_at')
            ->get()
            ->unique('id_packet')
            ->values(); // Reset collection keys

        // Use sample_type_id directly from ms_packet table
        foreach ($packets as $packet) {
            // If packet has sample_type_id, use it as array for compatibility with view
            if ($packet->sample_type_id) {
                $packet->sample_type_ids = [$packet->sample_type_id];
            } else {
                $packet->sample_type_ids = [];
            }
        }

        // Get laboratoriums ordered by name to ensure Kimia comes first, then Mikrobiologi
        $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')
            ->orderByRaw("CASE WHEN LOWER(nama_laboratorium) = 'kimia' THEN 1 WHEN LOWER(nama_laboratorium) = 'mikrobiologi' THEN 2 ELSE 3 END")
            ->get();

        // Prepare data_methods structure similar to sample create
        $data_methods = [];
        $methods_by_lab = [];

        foreach ($laboratoriums as $laboratorium) {
            $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $laboratorium->id_laboratorium)
                ->orderBy('ms_method.created_at')
                ->join('ms_method', function ($join) {
                    $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                        ->whereNull('tb_laboratorium_method.deleted_at')
                        ->whereNull('ms_method.deleted_at');
                })
                ->get();

            $methods = [];
            foreach ($laboratoriummethods as $laboratoriummethod) {
                // Get all sampletype_ids that have baku mutu for this method (Makanan: abaikan baku mutu tanpa jenis_makanan_id)
                $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
                    $laboratoriummethod->id_method,
                    $laboratorium->id_laboratorium
                );

                $methods[] = (object) [
                    'name_method' => $laboratoriummethod->params_method,
                    'params_method' => $laboratoriummethod->params_method,
                    'id_method' => $laboratoriummethod->id_method,
                    'price_method' => $laboratoriummethod->price_total_method,
                    'price_total_method' => $laboratoriummethod->price_total_method,
                    'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
                ];
            }

            $data_methods[] = (object) [
                'name' => $laboratorium->nama_laboratorium,
                'id_lab' => $laboratorium->id_laboratorium,
                'method' => $methods
            ];

            // Get methods grouped by lab for view (reuse the same data)
            $methods_by_lab[$laboratorium->id_laboratorium] = collect($methods);
        }

        $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);
        $methods_by_lab = [];
        foreach ($data_methods as $dm) {
            $methods_by_lab[$dm->id_lab] = collect($dm->method);
        }

        $all_jenis_makanan = JenisMakanan::all();
        $units = Unit::all();
        $libraries = Library::all();
        $kesmasSampleSettings = KesmasSampleNumberSettings::getSettings();
        $lab_kimia = Laboratorium::whereIn('kode_laboratorium', ['KMA', 'KIM'])->first();
        $lab_mikro = Laboratorium::where('kode_laboratorium', 'MBI')->first();

        return view('masterweb::module.admin.laboratorium.sample-draft.create', compact(
            'permohonan_uji',
            'sampletypes',
            'packets',
            'laboratoriums',
            'data_methods',
            'methods_by_lab',
            'all_jenis_makanan',
            'units',
            'libraries',
            'kesmasSampleSettings',
            'lab_kimia',
            'lab_mikro'
        ));
    }

    /**
     * Store a newly created draft sample.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id  permohonan_uji_id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'samples' => 'required|array|min:1',
            'samples.*.sample_type_id' => 'required|uuid',
            'samples.*.methods' => 'required|array|min:1',
            'samples.*.cost_samples' => 'required|numeric|min:0',
            'samples.*.packet_id' => 'nullable|uuid',
            'samples.*.titik_pengambilan' => 'nullable|string|max:500',
            'samples.*.nomor_spesimen_manual' => 'nullable|string|max:50',
            'samples.*.nomor_spesimen_mikro_manual' => 'nullable|string|max:50',
            'samples.*.manual_nomer_lab_kimia' => 'nullable|string|max:50',
            'samples.*.manual_nomer_lab_mikro' => 'nullable|string|max:50',
            'note_samples' => 'nullable|string',
            'cost_sampling' => 'nullable|numeric|min:0',
        ]);

            try {
                DB::beginTransaction();

                $user = Auth()->user();
                $permohonan_uji = PermohonanUji::with('customer')->findOrFail($id);

                $kesmas = KesmasSampleNumberSettings::getSettings();

                // Get nama_pelanggan from customer name
                $nama_pelanggan = null;
                if ($permohonan_uji->customer && isset($permohonan_uji->customer->name_customer)) {
                    $nama_pelanggan = $permohonan_uji->customer->name_customer;
                }

                // Get default program
                $program = Program::orderBy('created_at')->first();
                $default_program = $program ? $program->id_program : null;

                // Set default dates to now
                $now = Carbon::now();

                // Generate a single draft_group_id for all drafts in this submission
                $draft_group_id = Uuid::uuid4()->toString();

                $created_drafts = [];
                $packetCreatedAtCache = [];

                // Create draft sample for EACH sample configuration
                foreach ($request->samples as $sampleConfig) {
                $packetId = !empty($sampleConfig['packet_id']) ? $sampleConfig['packet_id'] : null;
                $packetCreatedAt = $this->resolvePacketBatchCreatedAt(
                    $packetId,
                    $draft_group_id,
                    $now,
                    $packetCreatedAtCache
                );
                $nomorSpesimenUrut = null;
                $nomorSpesimenMikroUrut = null;
                $nomorLabKimiaUrut = null;
                $nomorLabMikroUrut = null;
                if ($kesmas->is_nomor_sampel_manual) {
                    $rawK = preg_replace('/\D/', '', (string) ($sampleConfig['nomor_spesimen_manual'] ?? ''));
                    $nomorSpesimenUrut = ($rawK !== '') ? $rawK : null;

                    $rawM = preg_replace('/\D/', '', (string) ($sampleConfig['nomor_spesimen_mikro_manual'] ?? ''));
                    $nomorSpesimenMikroUrut = ($rawM !== '') ? $rawM : null;
                }
                if ($kesmas->is_nomor_laboratorium_manual) {
                    $rk = preg_replace('/\D/', '', (string) ($sampleConfig['manual_nomer_lab_kimia'] ?? ''));
                    $rm = preg_replace('/\D/', '', (string) ($sampleConfig['manual_nomer_lab_mikro'] ?? ''));
                    $nomorLabKimiaUrut = $rk !== '' ? $rk : null;
                    $nomorLabMikroUrut = $rm !== '' ? $rm : null;
                }

                // Create draft sample with individual configuration
                $draftRow = [
                    'id_sample_draft' => Uuid::uuid4()->toString(),
                    'permohonan_uji_id' => $id,
                    'draft_group_id' => $draft_group_id, // Same group ID for all drafts in this submission
                    'typesample_samples' => $sampleConfig['sample_type_id'],
                    'datesampling_samples' => $now,
                    'date_sending' => $now,
                    'titik_pengambilan' => $sampleConfig['titik_pengambilan'] ?? '', // From sample config (per sample type)
                    'cost_samples' => $sampleConfig['cost_samples'],
                    'note_samples' => $request->note_samples,
                    'packet_id' => $packetId,
                    'program_samples' => $default_program,
                    'is_sampling' => 1,
                    'cost_sampling_samples' => $request->cost_sampling ?? 20000, // From form input with default
                    'method_data' => null,
                    'status' => 'draft',
                    'created_by' => $user->id,
                    'pengambil_sampel' => $user->name ?? 'Petugas',
                    'name_pelanggan' => $nama_pelanggan, // Set from customer name
                ];
                $draftTable = 'tb_sample_draft';
                if (Schema::hasColumn($draftTable, 'nomor_spesimen_manual')) {
                    $draftRow['nomor_spesimen_manual'] = $nomorSpesimenUrut;
                }
                if (Schema::hasColumn($draftTable, 'nomor_spesimen_mikro_manual')) {
                    $draftRow['nomor_spesimen_mikro_manual'] = $nomorSpesimenMikroUrut;
                }
                if (Schema::hasColumn($draftTable, 'nomor_lab_kimia_manual')) {
                    $draftRow['nomor_lab_kimia_manual'] = $nomorLabKimiaUrut;
                }
                if (Schema::hasColumn($draftTable, 'nomor_lab_mikro_manual')) {
                    $draftRow['nomor_lab_mikro_manual'] = $nomorLabMikroUrut;
                }
                if (Schema::hasColumn($draftTable, 'is_nomor_sampel_manual')) {
                    $draftRow['is_nomor_sampel_manual'] = $kesmas->is_nomor_sampel_manual && ($nomorSpesimenUrut !== null || $nomorSpesimenMikroUrut !== null);
                }
                $draft = $this->createSampleDraftWithPacketTimestamp($draftRow, $packetCreatedAt);

                // Save methods for this specific draft
                if (isset($sampleConfig['methods']) && is_array($sampleConfig['methods'])) {
                    foreach ($sampleConfig['methods'] as $method_string) {
                        $parts = explode('_', $method_string);
                        if (count($parts) >= 3) {
                            SampleMethodDraft::create([
                                'id_sample_method_draft' => Uuid::uuid4()->toString(),
                                'sample_draft_id' => $draft->id_sample_draft,
                                'method_id' => $parts[0],
                                'laboratorium_id' => $parts[1],
                                'price_method' => (float)$parts[2],
                                'is_sub' => 0,
                            ]);
                        }
                    }
                }

                $created_drafts[] = $draft->id_sample_draft;
            }

            DB::commit();

            $total_created = count($created_drafts);
            $message = $total_created > 1
                ? "Berhasil menyimpan {$total_created} draft sample dengan konfigurasi berbeda!"
                : "Draft sample berhasil disimpan!";

                return response()->json([
                    'status' => true,
                    'pesan' => $message,
                    'total_created' => $total_created,
                    'draft_group_id' => $draft_group_id,
                    'ids' => $created_drafts
                ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menyimpan draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display a listing of draft samples for a permohonan uji.
     *
     * @param  string  $id  permohonan_uji_id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = Auth()->user();
        $permohonan_uji = PermohonanUji::findOrFail($id);

        $drafts = SampleDraft::where('permohonan_uji_id', $id)
            ->where('status', 'draft')
            ->with(['sampletype', 'packet', 'program', 'samplemethoddraft.method', 'samplemethoddraft.laboratorium'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Debug: Check if titik_pengambilan is loaded
        // foreach ($drafts as $draft) {
        //     \Log::info("Draft ID: {$draft->id_sample_draft}, Titik: {$draft->titik_pengambilan}");
        // }

        $list_name_petugas = $this->getPengambilSampelPetugasList();

        return view('masterweb::module.admin.laboratorium.sample-draft.index', compact(
            'user',
            'permohonan_uji',
            'drafts',
            'list_name_petugas'
        ));
    }

    /**
     * Confirm all draft samples for a permohonan uji
     *
     * @param  string  $id  permohonan_uji_id
     * @return \Illuminate\Http\Response
     */
    public function confirmAll(Request $request, $id)
    {
        [$jamPengambilan, $pengambilSampel] = $this->resolveConfirmPengambilanInput($request);

        try {
            DB::beginTransaction();

            $user = Auth()->user();
            
            // Get all draft samples for this permohonan uji
            $drafts = SampleDraft::where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->with(['samplemethoddraft', 'permohonanuji'])
                ->get();

            if ($drafts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Tidak ada draft sample yang dapat dikonfirmasi!'
                ], 400);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $packetCreatedAtCache = [];
            foreach ($drafts as $draft) {
                if (!empty($draft->packet_id) && !empty($draft->draft_group_id)) {
                    $this->resolvePacketBatchCreatedAt(
                        $draft->packet_id,
                        $draft->draft_group_id,
                        Carbon::parse($draft->created_at),
                        $packetCreatedAtCache
                    );
                }
            }

            foreach ($drafts as $draft) {
                try {
                    $permohonan_uji = $draft->permohonanuji;
                    $packetSampleCreatedAt = $this->resolvePacketBatchCreatedAt(
                        $draft->packet_id,
                        $draft->draft_group_id,
                        Carbon::parse($draft->created_at),
                        $packetCreatedAtCache
                    );

                    // Get unique lab IDs from samplemethoddraft
                    $lab_ids = [];
                    foreach ($draft->samplemethoddraft as $method_draft) {
                        if ($method_draft->laboratorium_id) {
                            $lab_ids[] = $method_draft->laboratorium_id;
                        }
                    }
                    $lab_ids = array_unique($lab_ids);
                    sort($lab_ids);

                    // Create sample for each lab
                    foreach ($lab_ids as $lab_id) {
                        $lab = Laboratorium::find($lab_id);
                        if (!$lab) continue;

                        // Get sample type code
                        $sample_type = SampleType::find($draft->typesample_samples);
                        $sample_type_code = $sample_type ? $sample_type->code_sample_type : 'AM';

                        // Get lab code
                        $lab_code = in_array($lab->kode_laboratorium, ['KMA', 'KIM']) ? '01' : ($lab->kode_laboratorium == 'MBI' ? '02' : '01');

                        $start_num = StartNum::join('ms_laboratorium', function ($join) {
                            $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                                ->whereNull('ms_laboratorium.deleted_at')
                                ->whereNull('ms_start_number.deleted_at');
                        })
                        ->where('id_laboratorium', $lab_id)
                        ->first();

                        $lab_num_rows = LabNum::where('lab_id', $lab_id)
                            ->where('sample_type_id', $draft->typesample_samples)
                            ->whereYear('year_lab_num', date('Y'))
                            ->get();

                        [
                            $code_sample,
                            $current_sample_urutan,
                            $isNomorSampelManual,
                            $lab_num_urutan,
                            $isNomorLabManual,
                            $sequence_detail_new,
                        ] = $this->resolveDraftSampleAndLabNumbers(
                            $draft,
                            $lab,
                            $sample_type_code,
                            $lab_code,
                            $start_num,
                            $lab_num_rows
                        );

                        // Calculate cost for this lab from samplemethoddraft
                        $lab_cost = 0;
                        foreach ($draft->samplemethoddraft as $method_draft) {
                            if ($method_draft->laboratorium_id == $lab_id) {
                                $lab_cost += (float)($method_draft->price_method ?? 0);
                            }
                        }

                        // Create sample (kimia/mikro dalam satu paket pakai created_at yang sama)
                        $sample = $this->createSampleWithPacketTimestamp([
                            'id_samples' => Uuid::uuid4()->toString(),
                            'permohonan_uji_id' => $draft->permohonan_uji_id,
                            'typesample_samples' => $draft->typesample_samples,
                            'codesample_samples' => $code_sample,
                            'count_id' => $current_sample_urutan,
                            'pengambil_sampel' => $pengambilSampel,
                            'packet_id' => $draft->packet_id,
                            'datesampling_samples' => $jamPengambilan->format('Y-m-d H:i:s'),
                            'date_sending' => $jamPengambilan->format('Y-m-d H:i:s'),
                            'titik_pengambilan' => $draft->titik_pengambilan,
                            'cost_samples' => $lab_cost > 0 ? $lab_cost : $draft->cost_samples,
                            'note_samples' => $draft->note_samples,
                            'program_samples' => $draft->program_samples,
                            'is_sampling' => 1,
                            'cost_sampling_samples' => $draft->cost_sampling_samples ?? 20000,
                            'is_nomor_sampel_manual' => $isNomorSampelManual,
                        ], $packetSampleCreatedAt);

                        // Store methods for this lab from samplemethoddraft
                        foreach ($draft->samplemethoddraft as $method_draft) {
                            if ($method_draft->laboratorium_id == $lab_id) {
                                SampleMethod::create([
                                    'id_sample_method' => Uuid::uuid4()->toString(),
                                    'sample_id' => $sample->id_samples,
                                    'method_id' => $method_draft->method_id,
                                    'laboratorium_id' => $method_draft->laboratorium_id,
                                    'price_method' => (int)($method_draft->price_method ?? 0),
                                    'is_sub' => $method_draft->is_sub ?? 0,
                                ]);
                            }
                        }

                        $sample->is_nomor_laboratorium_manual = $isNomorLabManual;
                        $sample->save();

                        $labNumRecord = LabNum::create([
                            'id_lab_num' => Uuid::uuid4()->toString(),
                            'sample_id' => $sample->id_samples,
                            'sample_type_id' => $draft->typesample_samples,
                            'lab_id' => $lab_id,
                            'mount_lab_num' => now()->format('m'),
                            'year_lab_num' => now()->format('Y'),
                            'permohonan_uji_id' => $sample->permohonan_uji_id,
                            'lab_number' => $lab_num_urutan,
                        ]);

                        if ($sequence_detail_new) {
                            $sequence_detail_new->update(['reference_id' => $labNumRecord->id_lab_num]);
                        }

                        if ($isNomorLabManual) {
                            $this->upsertNomerLabKesmasIfManual($draft->permohonan_uji_id, $lab_id, $lab_num_urutan);
                        }

                        
                        PenerimaanSample::create([
                            'id_sample_penerimaan' => Uuid::uuid4()->toString(),
                            'sample_id' => $sample->id_samples,
                            'laboratorium_id' => $lab_id,
                            'penerimaan_sample_date' => $jamPengambilan->format('Y-m-d H:i:s'),
                            'kelayakan_tempat_kemasan' => 'LAYAK',
                            'kelayakan_berat_vol' => 'LAYAK',
                        ]);

                        $this->createRegistrasiVerificationActivitySample(
                            $sample,
                            $permohonan_uji,
                            $user,
                            $jamPengambilan
                        );
                        $this->createPengambilanVerificationActivitySample(
                            $sample,
                            $jamPengambilan,
                            $pengambilSampel
                        );
                    }

                    // Update draft status
                    $draft->status = 'confirmed';
                    $draft->confirmed_by = $user->id;
                    $draft->confirmed_at = now();
                    $draft->save();

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Draft {$draft->id_sample_draft}: " . $e->getMessage();
                }
            }

            // Update total_harga on PermohonanUji after all drafts confirmed
            $total_cost = Sample::where('permohonan_uji_id', $id)->sum('cost_samples');
            $total_sampling_cost = Sample::where('permohonan_uji_id', $id)
                ->where('is_sampling', 1)
                ->sum('cost_sampling_samples');
            $permohonan_uji_update = PermohonanUji::where('id_permohonan_uji', $id)->first();
            if ($permohonan_uji_update) {
                $permohonan_uji_update->total_harga = $total_cost + $total_sampling_cost;
                $permohonan_uji_update->save();
            }

            DB::commit();

            $message = "Berhasil mengonfirmasi {$successCount} draft sample";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} draft gagal: " . implode('; ', $errors);
            }

            return response()->json([
                'status' => true,
                'pesan' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'redirect' => route('elits-samples.index', $id)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal mengonfirmasi draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Confirm a draft sample and save it to tb_samples.
     *
     * @param  string  $id  sample_draft_id
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, $id)
    {
        [$jamPengambilan, $pengambilSampel] = $this->resolveConfirmPengambilanInput($request);

        try {
            DB::beginTransaction();

            $user = Auth()->user();
            $draft = SampleDraft::findOrFail($id);

            if ($draft->status !== 'draft') {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Draft sample sudah dikonfirmasi atau dihapus!'
                ], 400);
            }

            $permohonan_uji = $draft->permohonanuji;

            // Load draft methods
            $draft->load('samplemethoddraft');

            // Get unique lab IDs from samplemethoddraft
            $lab_ids = [];
            foreach ($draft->samplemethoddraft as $method_draft) {
                if ($method_draft->laboratorium_id) {
                    $lab_ids[] = $method_draft->laboratorium_id;
                }
            }
            $lab_ids = array_unique($lab_ids);
            sort($lab_ids);

            $packetCreatedAtCache = [];
            $packetSampleCreatedAt = $this->resolvePacketBatchCreatedAt(
                $draft->packet_id,
                $draft->draft_group_id,
                Carbon::parse($draft->created_at),
                $packetCreatedAtCache
            );

            // Create sample for each lab
            foreach ($lab_ids as $lab_id) {
                $lab = Laboratorium::find($lab_id);
                if (!$lab) continue;

                // Get sample type code
                $sample_type = SampleType::find($draft->typesample_samples);
                $sample_type_code = $sample_type ? $sample_type->code_sample_type : 'AM';

                // Get lab code
                $lab_code = in_array($lab->kode_laboratorium, ['KMA', 'KIM']) ? '01' : ($lab->kode_laboratorium == 'MBI' ? '02' : '01');

                $start_num = StartNum::join('ms_laboratorium', function ($join) {
                    $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                        ->whereNull('ms_laboratorium.deleted_at')
                        ->whereNull('ms_start_number.deleted_at');
                })
                ->where('id_laboratorium', $lab_id)
                ->first();

                $lab_num_rows = LabNum::where('lab_id', $lab_id)
                    ->where('sample_type_id', $draft->typesample_samples)
                    ->whereYear('year_lab_num', date('Y'))
                    ->get();

                [
                    $code_sample,
                    $current_sample_urutan,
                    $isNomorSampelManual,
                    $lab_num_urutan,
                    $isNomorLabManual,
                    $sequence_detail_new,
                ] = $this->resolveDraftSampleAndLabNumbers(
                    $draft,
                    $lab,
                    $sample_type_code,
                    $lab_code,
                    $start_num,
                    $lab_num_rows
                );

                // Calculate cost for this lab from samplemethoddraft
                $lab_cost = 0;
                foreach ($draft->samplemethoddraft as $method_draft) {
                    if ($method_draft->laboratorium_id == $lab_id) {
                        $lab_cost += (float)($method_draft->price_method ?? 0);
                    }
                }

                // Create sample (kimia/mikro dalam satu paket pakai created_at yang sama)
                $sample = $this->createSampleWithPacketTimestamp([
                    'id_samples' => Uuid::uuid4()->toString(),
                    'permohonan_uji_id' => $draft->permohonan_uji_id,
                    'typesample_samples' => $draft->typesample_samples,
                    'codesample_samples' => $code_sample,
                    'count_id' => $current_sample_urutan,
                    'pengambil_sampel' => $pengambilSampel,
                    'packet_id' => $draft->packet_id,
                    'datesampling_samples' => $jamPengambilan->format('Y-m-d H:i:s'),
                    'date_sending' => $jamPengambilan->format('Y-m-d H:i:s'),
                    'titik_pengambilan' => $draft->titik_pengambilan,
                    'cost_samples' => $lab_cost > 0 ? $lab_cost : $draft->cost_samples,
                    'note_samples' => $draft->note_samples,
                    'program_samples' => $draft->program_samples,
                    'is_sampling' => 1,
                    'cost_sampling_samples' => $draft->cost_sampling_samples ?? 20000,
                    'is_nomor_sampel_manual' => $isNomorSampelManual,
                ], $packetSampleCreatedAt);

                // Store methods for this lab from samplemethoddraft
                foreach ($draft->samplemethoddraft as $method_draft) {
                    if ($method_draft->laboratorium_id == $lab_id) {
                        SampleMethod::create([
                            'id_sample_method' => Uuid::uuid4()->toString(),
                            'sample_id' => $sample->id_samples,
                            'method_id' => $method_draft->method_id,
                            'laboratorium_id' => $method_draft->laboratorium_id,
                            'price_method' => (int)($method_draft->price_method ?? 0),
                            'is_sub' => $method_draft->is_sub ?? 0,
                        ]);
                    }
                }

                $sample->is_nomor_laboratorium_manual = $isNomorLabManual;
                $sample->save();

                $labNumRecord = LabNum::create([
                    'id_lab_num' => Uuid::uuid4()->toString(),
                    'sample_id' => $sample->id_samples,
                    'sample_type_id' => $draft->typesample_samples,
                    'lab_id' => $lab_id,
                    'mount_lab_num' => now()->format('m'),
                    'year_lab_num' => now()->format('Y'),
                    'permohonan_uji_id' => $sample->permohonan_uji_id,
                    'lab_number' => $lab_num_urutan,
                ]);

                if ($sequence_detail_new) {
                    $sequence_detail_new->update(['reference_id' => $labNumRecord->id_lab_num]);
                }

                if ($isNomorLabManual) {
                    $this->upsertNomerLabKesmasIfManual($draft->permohonan_uji_id, $lab_id, $lab_num_urutan);
                }

                
                PenerimaanSample::create([
                    'id_sample_penerimaan' => Uuid::uuid4()->toString(),
                    'sample_id' => $sample->id_samples,
                    'laboratorium_id' => $lab_id,
                    'penerimaan_sample_date' => $jamPengambilan->format('Y-m-d H:i:s'),
                    'kelayakan_tempat_kemasan' => 'LAYAK',
                    'kelayakan_berat_vol' => 'LAYAK',
                ]);

                $this->createRegistrasiVerificationActivitySample(
                    $sample,
                    $permohonan_uji,
                    $user,
                    $jamPengambilan
                );
                $this->createPengambilanVerificationActivitySample(
                    $sample,
                    $jamPengambilan,
                    $pengambilSampel
                );
            }

            // Update draft status
            $draft->status = 'confirmed';
            $draft->confirmed_by = $user->id;
            $draft->confirmed_at = now();
            $draft->save();

            // Update total_harga on PermohonanUji
            $total_cost = Sample::where('permohonan_uji_id', $draft->permohonan_uji_id)->sum('cost_samples');
            $total_sampling_cost = Sample::where('permohonan_uji_id', $draft->permohonan_uji_id)
                ->where('is_sampling', 1)
                ->sum('cost_sampling_samples');
            $permohonan_uji->total_harga = $total_cost + $total_sampling_cost;
            $permohonan_uji->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'pesan' => 'Draft sample berhasil dikonfirmasi dan disimpan!',
                'redirect' => route('elits-samples.index', $draft->permohonan_uji_id)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal mengonfirmasi draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified draft sample.
     *
     * @param  string  $id  sample_draft_id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth()->user();
        $draft = SampleDraft::with([
            'sampletype',
            'packet',
            'program',
            'samplemethoddraft.method',
            'samplemethoddraft.laboratorium'
        ])->findOrFail($id);

        if ($draft->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya draft yang belum dikonfirmasi yang dapat diedit!');
        }

        $permohonan_uji = PermohonanUji::findOrFail($draft->permohonan_uji_id);

        return view('masterweb::module.admin.laboratorium.sample-draft.edit', compact(
            'user',
            'draft',
            'permohonan_uji'
        ));
    }

    /**
     * Update the specified draft sample.
     *
     * @param  string  $id  sample_draft_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
            'titik_pengambilan' => 'nullable|string|max:500',
            'cost_sampling_samples' => 'nullable|numeric|min:0',
            'note_samples' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $draft = SampleDraft::findOrFail($id);

            if ($draft->status !== 'draft') {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Hanya draft yang belum dikonfirmasi yang dapat diupdate!'
                ], 400);
            }

            // Update draft fields
            $draft->update([
                'titik_pengambilan' => $request->titik_pengambilan,
                'cost_sampling_samples' => $request->cost_sampling_samples ?? $draft->cost_sampling_samples,
                'note_samples' => $request->note_samples,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'pesan' => 'Draft sample berhasil diupdate!',
                'data' => $draft
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal mengupdate draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Duplicate the specified draft sample.
     *
     * @param  string  $id  sample_draft_id
     * @return \Illuminate\Http\Response
     */
    public function duplicate($id)
    {
        try {
            DB::beginTransaction();

            $user = Auth()->user();
            $original_draft = SampleDraft::with('samplemethoddraft')->findOrFail($id);

            if ($original_draft->status !== 'draft') {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Hanya draft yang belum dikonfirmasi yang dapat diduplikasi!'
                ], 400);
            }

            // Create duplicate draft
            $new_draft = SampleDraft::create([
                'id_sample_draft' => Uuid::uuid4()->toString(),
                'permohonan_uji_id' => $original_draft->permohonan_uji_id,
                'draft_group_id' => Uuid::uuid4()->toString(), // New group ID for duplicate
                'typesample_samples' => $original_draft->typesample_samples,
                'datesampling_samples' => $original_draft->datesampling_samples,
                'date_sending' => $original_draft->date_sending,
                'titik_pengambilan' => $original_draft->titik_pengambilan,
                'cost_samples' => $original_draft->cost_samples,
                'note_samples' => $original_draft->note_samples . ' (Copy)',
                'packet_id' => $original_draft->packet_id,
                'program_samples' => $original_draft->program_samples,
                'is_sampling' => $original_draft->is_sampling,
                'cost_sampling_samples' => $original_draft->cost_sampling_samples,
                'status' => 'draft',
                'created_by' => $user->id,
                'pengambil_sampel' => $original_draft->pengambil_sampel,
                'name_pelanggan' => $original_draft->name_pelanggan, // Copy name_pelanggan from original
            ]);

            // Duplicate methods
            foreach ($original_draft->samplemethoddraft as $method_draft) {
                SampleMethodDraft::create([
                    'id_sample_method_draft' => Uuid::uuid4()->toString(),
                    'sample_draft_id' => $new_draft->id_sample_draft,
                    'method_id' => $method_draft->method_id,
                    'laboratorium_id' => $method_draft->laboratorium_id,
                    'price_method' => $method_draft->price_method,
                    'is_sub' => $method_draft->is_sub,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'pesan' => 'Draft sample berhasil diduplikasi!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menduplikasi draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Duplicate all drafts in a group (by draft_group_id).
     *
     * @param  string  $draft_group_id
     * @return \Illuminate\Http\Response
     */
    public function duplicateGroup($draft_group_id)
    {
        try {
            DB::beginTransaction();

            $user = Auth()->user();

            // Get all drafts in the group
            $original_drafts = SampleDraft::with('samplemethoddraft')
                ->where('draft_group_id', $draft_group_id)
                ->where('status', 'draft')
                ->get();

            if ($original_drafts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Tidak ada draft dalam grup ini atau sudah dikonfirmasi!'
                ], 400);
            }

            // Generate new group ID for all duplicates
            $new_group_id = Uuid::uuid4()->toString();
            $created_count = 0;

            foreach ($original_drafts as $original_draft) {
                // Create duplicate draft with new group ID
                $new_draft = SampleDraft::create([
                    'id_sample_draft' => Uuid::uuid4()->toString(),
                    'permohonan_uji_id' => $original_draft->permohonan_uji_id,
                    'draft_group_id' => $new_group_id, // Same NEW group ID for all
                    'typesample_samples' => $original_draft->typesample_samples,
                    'datesampling_samples' => $original_draft->datesampling_samples,
                    'date_sending' => $original_draft->date_sending,
                    'titik_pengambilan' => $original_draft->titik_pengambilan,
                    'cost_samples' => $original_draft->cost_samples,
                    'note_samples' => $original_draft->note_samples . ' (Copy)',
                    'packet_id' => $original_draft->packet_id,
                    'program_samples' => $original_draft->program_samples,
                    'is_sampling' => $original_draft->is_sampling,
                    'cost_sampling_samples' => $original_draft->cost_sampling_samples,
                    'status' => 'draft',
                    'created_by' => $user->id,
                    'pengambil_sampel' => $original_draft->pengambil_sampel,
                    'name_pelanggan' => $original_draft->name_pelanggan, // Copy name_pelanggan from original
                ]);

                // Duplicate methods for this draft
                foreach ($original_draft->samplemethoddraft as $method_draft) {
                    SampleMethodDraft::create([
                        'id_sample_method_draft' => Uuid::uuid4()->toString(),
                        'sample_draft_id' => $new_draft->id_sample_draft,
                        'method_id' => $method_draft->method_id,
                        'laboratorium_id' => $method_draft->laboratorium_id,
                        'price_method' => $method_draft->price_method,
                        'is_sub' => $method_draft->is_sub,
                    ]);
                }

                $created_count++;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'pesan' => "Berhasil menduplikasi {$created_count} draft sample sebagai grup baru!",
                'created_count' => $created_count,
                'new_group_id' => $new_group_id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menduplikasi grup draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified draft sample.
     *
     * @param  string  $id  sample_draft_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $draft = SampleDraft::findOrFail($id);

            if ($draft->status !== 'draft') {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Draft sample sudah dikonfirmasi, tidak dapat dihapus!'
                ], 400);
            }

            $permohonan_uji_id = $draft->permohonan_uji_id;
            $draft->status = 'deleted';
            $draft->save();
            $draft->delete();

            return response()->json([
                'status' => true,
                'pesan' => 'Draft sample berhasil dihapus!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menghapus draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Print nota from draft samples.
     *
     * @param  string  $id  permohonan_uji_id
     * @return \Illuminate\Http\Response
     */
    public function printNota($id)
    {
        try {
            $permohonan_uji = PermohonanUji::with('customer')->findOrFail($id);

            // Get all draft samples for this permohonan
            $drafts = SampleDraft::where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->with(['sampletype', 'packet', 'samplemethoddraft.method', 'samplemethoddraft.laboratorium'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($drafts->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada draft sample untuk dicetak!');
            }

            // Group drafts by lab to build allLabsData structure
            $labs_data = [];
            $total_all = 0;

            // Get all unique labs from draft methods
            $all_labs = [];
            foreach ($drafts as $draft) {
                foreach ($draft->samplemethoddraft as $method_draft) {
                    if ($method_draft->laboratorium) {
                        $lab_id = $method_draft->laboratorium->id_laboratorium;
                        $lab_name = $method_draft->laboratorium->nama_laboratorium;
                        if (!isset($all_labs[$lab_id])) {
                            $all_labs[$lab_id] = [
                                'id' => $lab_id,
                                'name' => $lab_name,
                                'items' => []
                            ];
                        }
                    }
                }
            }

            // Build items for each lab - mirip dengan logika notaGabungan
            $no_lab = 1;

            // Group drafts by packet and lab untuk menghitung count
            foreach ($drafts as $draft) {
                $jenis_sampel_html = $draft->sampletype ?
                    '<strong>' . $draft->sampletype->code_sample_type . '</strong><br>' .
                    '<small>' . $draft->sampletype->name_sample_type . '</small>' : '-';

                $lokasi = $draft->titik_pengambilan ?? '-';

                // Jika menggunakan paket
                if ($draft->packet_id && $draft->packet) {
                    $packet_name = $draft->packet->name_packet;

                    // Group methods by lab untuk draft ini
                    $methods_by_lab = [];
                    foreach ($draft->samplemethoddraft as $method_draft) {
                        if ($method_draft->laboratorium) {
                            $lab_id = $method_draft->laboratorium->id_laboratorium;
                            if (!isset($methods_by_lab[$lab_id])) {
                                $methods_by_lab[$lab_id] = [];
                            }
                            $methods_by_lab[$lab_id][] = $method_draft;
                        }
                    }

                    // Untuk setiap lab, tambahkan 1 item paket dengan harga cost_samples
                    // Harga paket TIDAK dibagi per lab, tapi dimasukkan ke lab pertama yang ada
                    $lab_ids = array_keys($methods_by_lab);
                    if (!empty($lab_ids)) {
                        $first_lab_id = $lab_ids[0]; // Hanya lab pertama yang dapat harga paket

                        if (isset($all_labs[$first_lab_id])) {
                            $all_labs[$first_lab_id]['items'][] = [
                                'no' => $no_lab,
                                'jenis_sampel' => $jenis_sampel_html,
                                'lokasi' => $lokasi,
                                'name_item' => $packet_name,
                                'price' => (float)($draft->cost_samples ?? 0)
                            ];
                        }
                    }
                } else {
                    // Tidak menggunakan paket - hitung per parameter
                    foreach ($draft->samplemethoddraft as $method_draft) {
                        if ($method_draft->laboratorium && $method_draft->method) {
                            $lab_id = $method_draft->laboratorium->id_laboratorium;

                            if (isset($all_labs[$lab_id])) {
                                $all_labs[$lab_id]['items'][] = [
                                    'no' => $no_lab,
                                    'jenis_sampel' => $jenis_sampel_html,
                                    'lokasi' => $lokasi,
                                    'name_item' => $method_draft->method->params_method,
                                    'price' => (float)($method_draft->price_method ?? 0)
                                ];
                            }
                        }
                    }
                }

                $no_lab++;
            }

            // Build final allLabsData structure
            $allLabsData = [];
            foreach ($all_labs as $lab_id => $lab_info) {
                if (!empty($lab_info['items'])) {
                    $lab_total = array_sum(array_column($lab_info['items'], 'price'));
                    $total_all += $lab_total;

                    $allLabsData[$lab_info['name']] = [
                        'labTypeName' => $lab_info['name'],
                        'value_items' => $lab_info['items'],
                        'total' => $lab_total
                    ];
                }
            }

            // If no nota number yet, generate temporary one
            if (empty($permohonan_uji->nomor_nota)) {
                $permohonan_uji->nomor_nota = 'DRAFT-' . date('YmdHis');
            }

            // Generate PDF
            $pdf = \PDF::loadView('masterweb::module.admin.laboratorium.sample-draft.nota-draft', [
                'permohonan_uji' => $permohonan_uji,
                'allLabsData' => $allLabsData,
                'total_all' => $total_all
            ]);

            $pdf->setPaper('A4', 'portrait');

            $filename = 'Nota_Draft_' . $permohonan_uji->code_permohonan_uji . '_' . date('YmdHis') . '.pdf';

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencetak nota draft: ' . $e->getMessage());
        }
    }

    /**
     * Get code sample
     */
    private function getCodeSample($count, $lab_code = '01', $sample_type_code = '')
    {
        $code_number = str_pad((int)$count, 4, '0', STR_PAD_LEFT);
        $code_year = date('Y');
        $code = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;
        return $code;
    }

    /**
     * Angka urut nomor lab manual per draft (Kimia KMA / Mikro MBI).
     */
    private function draftManualLabUrut(SampleDraft $draft, Laboratorium $lab): ?int
    {
        $kode = strtoupper((string) ($lab->kode_laboratorium ?? ''));
        $raw = null;
        if (in_array($kode, ['KMA', 'KIM'], true)) {
            $raw = $draft->nomor_lab_kimia_manual;
        } elseif ($kode === 'MBI' || stripos((string) $lab->nama_laboratorium, 'mikro') !== false) {
            $raw = $draft->nomor_lab_mikro_manual;
        }
        if ($raw === null || $raw === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', (string) $raw);

        return $digits !== '' ? (int) $digits : null;
    }

    /**
     * @return array{0: string, 1: int, 2: bool} codesample_samples, count_id, is_nomor_sampel_manual
     */
    private function resolveDraftSampleCode(SampleDraft $draft, int $count, string $sample_type_code, string $lab_code): array
    {
        $kesmas = KesmasSampleNumberSettings::getSettings();
        $yearSending = Carbon::parse($draft->date_sending ?? now())->format('Y');
        
        $isManual = $kesmas->is_nomor_sampel_manual;
        $urut = null;
        
        if ($isManual) {
            if ($lab_code === '01') {
                $urut = (string) ($draft->nomor_spesimen_manual ?: $draft->nomor_lab_kimia_manual);
            } elseif ($lab_code === '02') {
                $urut = (string) ($draft->nomor_spesimen_mikro_manual ?: $draft->nomor_spesimen_manual ?: $draft->nomor_lab_mikro_manual);
            } else {
                $urut = (string) ($draft->nomor_spesimen_manual ?: $draft->nomor_lab_kimia_manual);
            }
        }

        if ($isManual && !empty($urut)) {
            $paddedUrut = str_pad($urut, 4, '0', STR_PAD_LEFT);
            $code = $sample_type_code . '.' . ($lab_code ?: '03') . '/' . $paddedUrut . '/' . $yearSending;
            $cid = (int) preg_replace('/\D/', '', $urut);
            if ($cid < 1) {
                $cid = $count + 1;
            }

            return [$code, $cid, true];
        }
        $current_sample_urutan = $count + 1;
        $code = $this->getCodeSample($current_sample_urutan, $lab_code, $sample_type_code);

        return [$code, $current_sample_urutan, false];
    }

    /**
     * Alokasi nomor sampel + lab draft: urutan global (satu pool dengan klinik) saat mode otomatis.
     *
     * @param  mixed  $start_num
     * @param  mixed  $labNumRows
     * @return array{
     *   0: string,
     *   1: int,
     *   2: bool,
     *   3: int,
     *   4: bool,
     *   5: GlobalLabSequenceDetail|null
     * }
     */
    private function resolveDraftSampleAndLabNumbers(
        SampleDraft $draft,
        Laboratorium $lab,
        string $sample_type_code,
        string $lab_code,
        $start_num,
        $labNumRows
    ): array {
        $kesmas = KesmasSampleNumberSettings::getSettings();
        $year = (int) Carbon::parse($draft->date_sending ?? now())->format('Y');
        $labId = $lab->id_laboratorium;

        [$lab_num_urutan, $isNomorLabManual] = $this->resolveDraftLabNumber($draft, $lab, $start_num, $labNumRows);

        if ($kesmas->is_nomor_sampel_manual) {
            [$code_sample, $current_sample_urutan, $isNomorSampelManual] = $this->resolveDraftSampleCode(
                $draft,
                0,
                $sample_type_code,
                $lab_code
            );
            if ($isNomorSampelManual && $current_sample_urutan > 0) {
                GlobalLabSequence::raiseLastNumberToAtLeast($current_sample_urutan, $year);
            }

            return [
                $code_sample,
                $current_sample_urutan,
                $isNomorSampelManual,
                $lab_num_urutan,
                $isNomorLabManual,
                null,
            ];
        }

        if ($isNomorLabManual) {
            $seq = (int) $lab_num_urutan;
            if ($seq < 1) {
                throw new \InvalidArgumentException('Nomor laboratorium manual tidak valid.');
            }
            GlobalLabSequence::raiseLastNumberToAtLeast($seq, $year);
            $code_sample = $this->getCodeSample($seq, $lab_code, $sample_type_code);

            return [$code_sample, $seq, false, $lab_num_urutan, true, null];
        }

        $current_global = GlobalLabSequence::getCurrentNumber($year);
        if ($current_global == 0 && $start_num && $year === (int) ($start_num->year_start_number ?? $year)) {
            GlobalLabSequence::raiseLastNumberToAtLeast((int) ($start_num->count_start_number ?? 0), $year);
        }

        $seq = GlobalLabSequence::getNextNumber($year, $labId, 'lab', null);
        $sequence_detail_new = GlobalLabSequenceDetail::where('year', $year)
            ->where('sequence_number', $seq)
            ->where('lab_id', $labId)
            ->where('lab_type', 'lab')
            ->orderBy('created_at', 'desc')
            ->first();

        $code_sample = $this->getCodeSample($seq, $lab_code, $sample_type_code);

        return [$code_sample, $seq, false, $seq, false, $sequence_detail_new];
    }

    /**
     * @param  mixed  $labNumRows  collection of LabNum rows for lab/year
     * @return array{0: int, 1: bool} lab_number, is_nomor_laboratorium_manual
     */
    private function resolveDraftLabNumber(SampleDraft $draft, Laboratorium $lab, $start_num, $labNumRows): array
    {
        $kesmas = KesmasSampleNumberSettings::getSettings();
        $manual = $this->draftManualLabUrut($draft, $lab);
        if ($kesmas->is_nomor_laboratorium_manual && $manual !== null && $manual > 0) {
            return [$manual, true];
        }
        $lab_num_urutan = $labNumRows->max('lab_number') ?? 0;
        if ($lab_num_urutan > 0) {
            $lab_num_urutan = $lab_num_urutan + 1;
        } else {
            if ($start_num && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
                $lab_num_urutan = ($start_num->count_start_number ?? 0) + 1;
            } else {
                $lab_num_urutan = 1;
            }
        }

        return [$lab_num_urutan, false];
    }

    private function upsertNomerLabKesmasIfManual(string $permohonanUjiId, string $labId, int $nomerLab): void
    {
        if ($nomerLab < 1) {
            return;
        }
        $exists = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiId)
            ->where('laboratorium_id', $labId)
            ->first();
        if ($exists) {
            return;
        }
        NomerLabKesmas::create([
            'id' => Uuid::uuid4()->toString(),
            'permohonan_uji_id' => $permohonanUjiId,
            'laboratorium_id' => $labId,
            'nomer_lab' => $nomerLab,
            'year' => (int) date('Y'),
        ]);
    }

    /**
     * Satu created_at untuk semua entitas dalam batch paket (draft_group_id + packet_id).
     */
    private function resolvePacketBatchCreatedAt(
        ?string $packetId,
        ?string $draftGroupId,
        Carbon $timestamp,
        array &$cache
    ): ?Carbon {
        if (empty($packetId) || empty($draftGroupId)) {
            return null;
        }

        $key = $draftGroupId . ':' . (string) $packetId;
        if (!isset($cache[$key])) {
            $cache[$key] = $timestamp->copy();
        }

        return $cache[$key]->copy();
    }

    private function createSampleDraftWithPacketTimestamp(array $attributes, ?Carbon $packetCreatedAt): SampleDraft
    {
        $draft = new SampleDraft($attributes);
        if ($packetCreatedAt !== null) {
            $draft->created_at = $packetCreatedAt;
            $draft->updated_at = $packetCreatedAt;
        }
        $draft->save();

        return $draft;
    }

    private function createSampleWithPacketTimestamp(array $attributes, ?Carbon $packetCreatedAt): Sample
    {
        $sample = new Sample($attributes);
        if ($packetCreatedAt !== null) {
            $sample->created_at = $packetCreatedAt;
            $sample->updated_at = $packetCreatedAt;
        }
        $sample->save();

        return $sample;
    }

    /**
     * Daftar petugas pengambil sampel — sama dengan halaman verifikasi (register + kimia + mikro).
     */
    private function getPengambilSampelPetugasList(): array
    {
        $activities = VerificationActivity::orderBy('id')->get();
        $registerRow = $activities->get(0);
        $labRow = $activities->get(5);

        $petugasRegister = explode(', ', (string) (optional($registerRow)->register ?? ''));
        $petugasKimia = explode(', ', (string) (optional($labRow)->kimia ?? ''));
        $petugasMikro = explode(', ', (string) (optional($labRow)->mikro ?? ''));

        $merged = array_unique(array_merge($petugasRegister, $petugasKimia, $petugasMikro));

        return array_values(array_filter($merged, function ($name) {
            return trim((string) $name) !== '';
        }));
    }

    /**
     * @return array{0: Carbon, 1: string}
     */
    private function resolveConfirmPengambilanInput(Request $request): array
    {
        $allowed = $this->getPengambilSampelPetugasList();

        $request->validate([
            'jam_pengambilan' => 'required|date',
            'pengambil_sampel' => ['required', 'string', 'max:255', Rule::in($allowed)],
        ], [
            'jam_pengambilan.required' => 'Jam pengambilan sampel wajib diisi.',
            'jam_pengambilan.date' => 'Format jam pengambilan sampel tidak valid.',
            'pengambil_sampel.required' => 'Petugas pengambil sampel wajib dipilih.',
            'pengambil_sampel.in' => 'Petugas pengambil sampel tidak valid.',
        ]);

        return [
            $this->parseJamPengambilanInput((string) $request->input('jam_pengambilan')),
            trim((string) $request->input('pengambil_sampel')),
        ];
    }

    private function parseJamPengambilanInput(string $value): Carbon
    {
        $value = trim($value);
        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i', 'd/m/Y H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                // coba format berikutnya
            }
        }

        return Carbon::parse($value);
    }

    /**
     * Buat VerificationActivitySample step 1 (Pendaftaran/Registrasi) per sampel final.
     */
    private function createRegistrasiVerificationActivitySample(
        Sample $sample,
        PermohonanUji $permohonanUji,
        $user,
        $dateSending = null
    ): void {
        $exists = VerificationActivitySample::where('id_sample', $sample->id_samples)
            ->where('id_verification_activity', 1)
            ->exists();

        if ($exists) {
            return;
        }

        $startDate = $dateSending
            ?? $sample->date_sending
            ?? $permohonanUji->date_permohonan_uji
            ?? now();

        $startDate = Carbon::parse($startDate)->format('Y-m-d H:i:s');

        $namaPetugas = $permohonanUji->petugas_penerima
            ?? $sample->pengambil_sampel
            ?? ($user->name ?? null)
            ?? 'Petugas';

        VerificationActivitySample::create([
            'id' => Uuid::uuid4()->toString(),
            'id_verification_activity' => 1,
            'id_sample' => $sample->id_samples,
            'start_date' => $startDate,
            'stop_date' => Carbon::parse($startDate)->addMinutes(5)->format('Y-m-d H:i:s'),
            'nama_petugas' => $namaPetugas,
            'is_done' => true,
        ]);
    }

    /**
     * Buat VerificationActivitySample step 6 (Pengambilan Sampel).
     */
    private function createPengambilanVerificationActivitySample(
        Sample $sample,
        Carbon $jamPengambilan,
        string $pengambilSampel
    ): void {
        $exists = VerificationActivitySample::where('id_sample', $sample->id_samples)
            ->where('id_verification_activity', 6)
            ->exists();

        if ($exists) {
            return;
        }

        $jam = $jamPengambilan->format('Y-m-d H:i:s');

        VerificationActivitySample::create([
            'id' => Uuid::uuid4()->toString(),
            'id_verification_activity' => 6,
            'id_sample' => $sample->id_samples,
            'start_date' => $jam,
            'stop_date' => $jam,
            'nama_petugas' => $pengambilSampel,
            'is_done' => true,
        ]);
    }
}