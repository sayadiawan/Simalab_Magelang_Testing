<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Smt\Masterweb\Helpers\ActivityLogger;
use Smt\Masterweb\Models\PengarsipanDokumen;

class PengarsipanDokumenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Dokumen Arsip Tambahan.');
        }

        $docQ = trim((string) $request->get('q', ''));
        $docBidang = trim((string) $request->get('bidang', ''));
        $dokumenResults = $this->searchDokumen($docQ, $docBidang);
        $stats = $this->buildStats();
        $suggestedNomor = [
            'klinik' => $this->suggestNextNomor('klinik'),
            'kesmas' => $this->suggestNextNomor('kesmas'),
            'umum' => $this->suggestNextNomor('umum'),
        ];

        return view('masterweb::module.admin.pengarsipan.dokumen', [
            'dokumenResults' => $dokumenResults,
            'stats' => $stats,
            'suggestedNomor' => $suggestedNomor,
            'q' => $docQ,
            'bidang' => $docBidang,
        ]);
    }

    public function store(Request $request)
    {
        $this->assertCanManage('create');

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
            'judul' => 'required|string|max:255',
            'nomor_arsip' => 'nullable|string|max:100',
            'bidang' => 'required|in:klinik,kesmas,umum',
            'keterangan' => 'nullable|string|max:2000',
            'ref_bidang' => 'nullable|in:klinik,kesmas',
            'ref_id' => 'nullable|string|max:64',
            'ref_label' => 'nullable|string|max:255',
        ]);

        $bidang = $validated['bidang'];
        $tahun = (int) date('Y');
        $nomor = trim((string) ($validated['nomor_arsip'] ?? ''));
        if ($nomor === '') {
            $nomor = $this->suggestNextNomor($bidang, $tahun);
        }

        if ($this->nomorArsipExists($nomor)) {
            return back()
                ->withInput()
                ->with('error', 'Nomor arsip "' . $nomor . '" sudah digunakan. Gunakan nomor lain.');
        }

        $file = $request->file('file');
        $storedPath = $file->store('pengarsipan-dokumen/' . $tahun, 'public');
        $user = auth()->user();

        $dokumen = PengarsipanDokumen::create([
            'nomor_arsip' => $nomor,
            'bidang' => $bidang,
            'judul' => $validated['judul'],
            'keterangan' => $validated['keterangan'] ?? null,
            'ref_bidang' => $validated['ref_bidang'] ?? null,
            'ref_id' => $validated['ref_id'] ?? null,
            'ref_label' => $validated['ref_label'] ?? null,
            'file_path' => $storedPath,
            'file_name_original' => $file->getClientOriginalName(),
            'file_mime' => $file->getMimeType(),
            'file_size' => (int) $file->getSize(),
            'tahun' => $tahun,
            'uploaded_by' => optional($user)->id,
            'uploaded_by_name' => optional($user)->name,
        ]);

        ActivityLogger::record([
            'user_id' => optional($user)->id,
            'user_name' => optional($user)->name,
            'username' => optional($user)->username,
            'privilege_level' => optional(optional($user)->getlevel)->level,
            'action' => 'create',
            'bidang' => $bidang,
            'module' => 'pengarsipan-dokumen',
            'description' => 'Upload dokumen arsip: ' . $nomor . ' — ' . $validated['judul'],
            'subject_type' => 'pengarsipan_dokumen',
            'subject_id' => $dokumen->id_pengarsipan_dokumen,
            'metadata' => [
                'nomor_arsip' => $nomor,
                'file_name' => $dokumen->file_name_original,
            ],
        ]);

        return redirect()
            ->route('pengarsipan-dokumen.index')
            ->with('success', 'Dokumen arsip berhasil diunggah dengan nomor ' . $nomor . '.');
    }

    public function updateNomor(Request $request, $id)
    {
        $this->assertCanManage('update');

        $dokumen = PengarsipanDokumen::query()->findOrFail($id);

        $validated = $request->validate([
            'nomor_arsip' => 'required|string|max:100',
            'judul' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:2000',
        ]);

        $nomor = trim($validated['nomor_arsip']);
        if ($this->nomorArsipExists($nomor, $dokumen->id_pengarsipan_dokumen)) {
            return back()->with('error', 'Nomor arsip "' . $nomor . '" sudah digunakan.');
        }

        $oldNomor = $dokumen->nomor_arsip;
        $dokumen->nomor_arsip = $nomor;
        if (!empty($validated['judul'])) {
            $dokumen->judul = $validated['judul'];
        }
        if (array_key_exists('keterangan', $validated)) {
            $dokumen->keterangan = $validated['keterangan'];
        }
        $dokumen->save();

        $user = auth()->user();
        ActivityLogger::record([
            'user_id' => optional($user)->id,
            'user_name' => optional($user)->name,
            'username' => optional($user)->username,
            'privilege_level' => optional(optional($user)->getlevel)->level,
            'action' => 'update',
            'bidang' => $dokumen->bidang,
            'module' => 'pengarsipan-dokumen',
            'description' => 'Ubah penomoran arsip: ' . $oldNomor . ' → ' . $nomor,
            'subject_type' => 'pengarsipan_dokumen',
            'subject_id' => $dokumen->id_pengarsipan_dokumen,
        ]);

        return back()->with('success', 'Penomoran dokumen arsip berhasil diperbarui.');
    }

    public function download($id)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Dokumen Arsip Tambahan.');
        }

        $dokumen = PengarsipanDokumen::query()->findOrFail($id);

        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'Berkas dokumen tidak ditemukan.');
        }

        $user = auth()->user();
        ActivityLogger::record([
            'user_id' => optional($user)->id,
            'user_name' => optional($user)->name,
            'username' => optional($user)->username,
            'privilege_level' => optional(optional($user)->getlevel)->level,
            'action' => 'export',
            'bidang' => $dokumen->bidang,
            'module' => 'pengarsipan-dokumen',
            'description' => 'Unduh dokumen arsip: ' . ($dokumen->nomor_arsip ?: $dokumen->judul),
            'subject_type' => 'pengarsipan_dokumen',
            'subject_id' => $dokumen->id_pengarsipan_dokumen,
        ]);

        return Storage::disk('public')->download(
            $dokumen->file_path,
            $dokumen->file_name_original
        );
    }

    public function destroy($id)
    {
        $this->assertCanManage('update');

        $dokumen = PengarsipanDokumen::query()->findOrFail($id);
        $nomor = $dokumen->nomor_arsip;
        $bidang = $dokumen->bidang;
        $dokumen->delete();

        $user = auth()->user();
        ActivityLogger::record([
            'user_id' => optional($user)->id,
            'user_name' => optional($user)->name,
            'username' => optional($user)->username,
            'privilege_level' => optional(optional($user)->getlevel)->level,
            'action' => 'delete',
            'bidang' => $bidang,
            'module' => 'pengarsipan-dokumen',
            'description' => 'Hapus dokumen arsip: ' . ($nomor ?: $id),
            'subject_type' => 'pengarsipan_dokumen',
            'subject_id' => $id,
        ]);

        return back()->with('success', 'Dokumen arsip berhasil dihapus.');
    }

    /**
     * @return array<string, int>
     */
    private function buildStats()
    {
        $total = 0;
        $tahunIni = 0;

        if (Schema::hasTable('tb_pengarsipan_dokumen')) {
            $total = PengarsipanDokumen::query()->count();
            $tahunIni = PengarsipanDokumen::query()
                ->where('tahun', (int) date('Y'))
                ->count();
        }

        return [
            'total' => $total,
            'tahun_ini' => $tahunIni,
        ];
    }

    /**
     * @param string $action
     * @return void
     */
    private function assertCanManage($action)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Dokumen Arsip Tambahan.');
        }

        if (!getAction($action)) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola dokumen arsip.');
        }
    }

    /**
     * @param string|null $excludeId
     * @return bool
     */
    private function nomorArsipExists($nomor, $excludeId = null)
    {
        if (!Schema::hasTable('tb_pengarsipan_dokumen') || trim($nomor) === '') {
            return false;
        }

        $query = PengarsipanDokumen::query()->where('nomor_arsip', $nomor);
        if ($excludeId) {
            $query->where('id_pengarsipan_dokumen', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * @param string $bidang
     * @param int|null $tahun
     * @return string
     */
    private function suggestNextNomor($bidang, $tahun = null)
    {
        $tahun = $tahun ?: (int) date('Y');
        $prefixMap = [
            'klinik' => 'KLI',
            'kesmas' => 'KES',
            'umum' => 'UMM',
        ];
        $code = $prefixMap[$bidang] ?? 'UMM';
        $prefix = 'ARSIP/' . $code . '/' . $tahun . '/';

        if (!Schema::hasTable('tb_pengarsipan_dokumen')) {
            return $prefix . '0001';
        }

        $latest = PengarsipanDokumen::query()
            ->where('bidang', $bidang)
            ->where('tahun', $tahun)
            ->where('nomor_arsip', 'like', $prefix . '%')
            ->orderByDesc('nomor_arsip')
            ->value('nomor_arsip');

        $seq = 1;
        if ($latest && preg_match('#/(\d+)$#', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $docQ
     * @param string $docBidang
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    private function searchDokumen($docQ, $docBidang = '')
    {
        if (!Schema::hasTable('tb_pengarsipan_dokumen')) {
            return collect();
        }

        $query = PengarsipanDokumen::query()->orderByDesc('created_at');

        if ($docBidang !== '' && in_array($docBidang, ['klinik', 'kesmas', 'umum'], true)) {
            $query->where('bidang', $docBidang);
        }

        if ($docQ !== '') {
            $like = '%' . $docQ . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('nomor_arsip', 'like', $like)
                    ->orWhere('judul', 'like', $like)
                    ->orWhere('keterangan', 'like', $like)
                    ->orWhere('ref_label', 'like', $like)
                    ->orWhere('file_name_original', 'like', $like);
            });
        }

        return $query->paginate(20)->appends([
            'q' => $docQ,
            'bidang' => $docBidang,
        ]);
    }
}
