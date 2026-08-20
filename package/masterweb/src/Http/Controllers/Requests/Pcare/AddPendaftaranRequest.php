<?php

namespace Smt\Masterweb\Http\Controllers\Requests\Pcare;

use Illuminate\Foundation\Http\FormRequest;

class AddPendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sesuaikan ke policy/auth kamu
    }

    public function rules(): array
    {
        return [
            'kdProviderPeserta' => 'required|string|max:20',
            'tglDaftar'         => 'required|date_format:Y-m-d',
            'noKartu'           => 'required|string|max:20',
            'kdPoli'            => 'required|string|max:10',
            'keluhan'           => 'nullable|string|max:500',
            'kunjSakit'         => 'required|in:1,2', // 1=sakit,2=sehat
            'sistole'           => 'nullable|integer|min:50|max:250',
            'diastole'          => 'nullable|integer|min:30|max:150',
            'beratBadan'        => 'nullable|numeric|min:1|max:500',
            'tinggiBadan'       => 'nullable|numeric|min:30|max:250',
            'respRate'          => 'nullable|integer|min:5|max:60',
            'heartRate'         => 'nullable|integer|min:20|max:220',
            'rujukBalik'        => 'nullable|in:0,1',
            'kdTkp'             => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'tglDaftar.date_format' => 'Format tglDaftar harus Y-m-d (contoh 2025-08-30).',
        ];
    }
}