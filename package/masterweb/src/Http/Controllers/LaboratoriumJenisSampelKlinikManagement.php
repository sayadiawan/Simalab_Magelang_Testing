<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Smt\Masterweb\Models\JenisSampelKlinik;

class LaboratoriumJenisSampelKlinikManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = JenisSampelKlinik::orderBy('sort_order', 'asc')
            ->orderBy('name_jenis_sampel_klinik', 'asc')
            ->get();

        return view('masterweb::module.admin.laboratorium.jenis-sampel-klinik.list', compact('items'));
    }

    public function create()
    {
        return view('masterweb::module.admin.laboratorium.jenis-sampel-klinik.add');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_jenis_sampel_klinik' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ms_jenis_sampel_klinik', 'name_jenis_sampel_klinik')->whereNull('deleted_at'),
            ],
            'code_jenis_sampel_klinik' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name_jenis_sampel_klinik.required' => 'Nama jenis sampel wajib diisi.',
            'name_jenis_sampel_klinik.unique' => 'Nama jenis sampel sudah ada.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $item = new JenisSampelKlinik();
            $item->name_jenis_sampel_klinik = trim($request->post('name_jenis_sampel_klinik'));
            $item->code_jenis_sampel_klinik = $request->post('code_jenis_sampel_klinik')
                ? trim($request->post('code_jenis_sampel_klinik'))
                : null;
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->sort_order = (int) ($request->post('sort_order') ?: 0);
            $item->save();

            DB::commit();

            return redirect()
                ->route('elits-jenis-sampel-klinik.index')
                ->with('status', 'Jenis sampel berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', $th->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $item = JenisSampelKlinik::findOrFail($id);

        return view('masterweb::module.admin.laboratorium.jenis-sampel-klinik.edit', compact('item', 'id'));
    }

    public function update(Request $request, $id)
    {
        $item = JenisSampelKlinik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_jenis_sampel_klinik' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ms_jenis_sampel_klinik', 'name_jenis_sampel_klinik')
                    ->ignore($id, 'id_jenis_sampel_klinik')
                    ->whereNull('deleted_at'),
            ],
            'code_jenis_sampel_klinik' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name_jenis_sampel_klinik.required' => 'Nama jenis sampel wajib diisi.',
            'name_jenis_sampel_klinik.unique' => 'Nama jenis sampel sudah ada.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $item->name_jenis_sampel_klinik = trim($request->post('name_jenis_sampel_klinik'));
            $item->code_jenis_sampel_klinik = $request->post('code_jenis_sampel_klinik')
                ? trim($request->post('code_jenis_sampel_klinik'))
                : null;
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->sort_order = (int) ($request->post('sort_order') ?: 0);
            $item->save();

            DB::commit();

            return redirect()
                ->route('elits-jenis-sampel-klinik.index')
                ->with('status', 'Jenis sampel berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', $th->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $item = JenisSampelKlinik::findOrFail($id);
        $item->delete();

        return redirect()
            ->route('elits-jenis-sampel-klinik.index')
            ->with('status', 'Jenis sampel berhasil dihapus.');
    }
}
