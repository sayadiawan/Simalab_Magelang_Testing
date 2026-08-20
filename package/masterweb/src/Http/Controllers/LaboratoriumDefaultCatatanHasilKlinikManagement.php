<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Smt\Masterweb\Models\DefaultCatatanHasilKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;

class LaboratoriumDefaultCatatanHasilKlinikManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = DefaultCatatanHasilKlinik::with('parameterSatuanKlinik')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('masterweb::module.admin.laboratorium.default-catatan-hasil-klinik.list', compact('items'));
    }

    public function create()
    {
        $parameters = ParameterSatuanKlinik::orderBy('name_parameter_satuan_klinik', 'asc')->get();

        return view('masterweb::module.admin.laboratorium.default-catatan-hasil-klinik.add', compact('parameters'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parameter_satuan_klinik' => [
                'required',
                Rule::unique('ms_default_catatan_hasil_klinik', 'parameter_satuan_klinik')->whereNull('deleted_at'),
            ],
            'catatan_default' => 'required',
        ], [
            'parameter_satuan_klinik.required' => 'Parameter satuan klinik wajib dipilih.',
            'parameter_satuan_klinik.unique' => 'Default catatan untuk parameter ini sudah ada.',
            'catatan_default.required' => 'Catatan default wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $item = new DefaultCatatanHasilKlinik();
            $item->parameter_satuan_klinik = $request->post('parameter_satuan_klinik');
            $item->catatan_default = $request->post('catatan_default');
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->sort_order = (int) ($request->post('sort_order') ?: 0);
            $item->save();

            DB::commit();

            return redirect()
                ->route('elits-default-catatan-hasil-klinik.index')
                ->with('status', 'Default catatan hasil berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', $th->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $item = DefaultCatatanHasilKlinik::findOrFail($id);
        $parameters = ParameterSatuanKlinik::orderBy('name_parameter_satuan_klinik', 'asc')->get();

        return view('masterweb::module.admin.laboratorium.default-catatan-hasil-klinik.edit', compact('item', 'parameters', 'id'));
    }

    public function update(Request $request, $id)
    {
        $item = DefaultCatatanHasilKlinik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'parameter_satuan_klinik' => [
                'required',
                Rule::unique('ms_default_catatan_hasil_klinik', 'parameter_satuan_klinik')
                    ->ignore($id, 'id_default_catatan_hasil_klinik')
                    ->whereNull('deleted_at'),
            ],
            'catatan_default' => 'required',
        ], [
            'parameter_satuan_klinik.required' => 'Parameter satuan klinik wajib dipilih.',
            'parameter_satuan_klinik.unique' => 'Default catatan untuk parameter ini sudah ada.',
            'catatan_default.required' => 'Catatan default wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $item->parameter_satuan_klinik = $request->post('parameter_satuan_klinik');
            $item->catatan_default = $request->post('catatan_default');
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->sort_order = (int) ($request->post('sort_order') ?: 0);
            $item->save();

            DB::commit();

            return redirect()
                ->route('elits-default-catatan-hasil-klinik.index')
                ->with('status', 'Default catatan hasil berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', $th->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $item = DefaultCatatanHasilKlinik::findOrFail($id);
        $item->delete();

        return redirect()
            ->route('elits-default-catatan-hasil-klinik.index')
            ->with('status', 'Default catatan hasil berhasil dihapus.');
    }
}
