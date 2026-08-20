<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\Wilayah;

class WilayahController extends Controller
{
    /**
     * Get all provinces
     */
    public function getProvinsi()
    {
        try {
            $provinsi = Wilayah::getProvinsi();
            return response()->json($provinsi, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get kabupaten/kota by province ID
     */
    public function getKabupaten($provinsi_id)
    {
        try {
            $provinsi = Wilayah::find($provinsi_id);
            
            if (!$provinsi) {
                return response()->json(['error' => 'Provinsi tidak ditemukan'], 404);
            }

            $kabupaten = Wilayah::getKabupatenByProvinsi($provinsi->wilayah_kode);
            return response()->json($kabupaten, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get kecamatan by kabupaten ID
     */
    public function getKecamatan($kabupaten_id)
    {
        try {
            $kabupaten = Wilayah::find($kabupaten_id);
            
            if (!$kabupaten) {
                return response()->json(['error' => 'Kabupaten tidak ditemukan'], 404);
            }

            $kecamatan = Wilayah::getKecamatanByKabupaten($kabupaten->wilayah_kode);
            return response()->json($kecamatan, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get desa/kelurahan by kecamatan ID
     */
    public function getDesa($kecamatan_id)
    {
        try {
            $kecamatan = Wilayah::find($kecamatan_id);
            
            if (!$kecamatan) {
                return response()->json(['error' => 'Kecamatan tidak ditemukan'], 404);
            }

            $desa = Wilayah::getDesaByKecamatan($kecamatan->wilayah_kode);
            return response()->json($desa, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search wilayah (autocomplete)
     */
    public function search(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');
            $limit = $request->get('limit', 10);

            $results = Wilayah::searchWilayah($keyword, $limit);
            return response()->json($results, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parent IDs from wilayah ID
     */
    public function getParents($wilayah_id)
    {
        try {
            $wilayah = Wilayah::find($wilayah_id);
            
            if (!$wilayah) {
                return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
            }

            $parents = Wilayah::getParentIds($wilayah->wilayah_kode, $wilayah->tipe);
            
            // Add the selected wilayah ID based on its type
            if ($wilayah->tipe == 'DESA') {
                $parents['desa_id'] = $wilayah_id;
            } elseif ($wilayah->tipe == 'KEC') {
                $parents['kecamatan_id'] = $wilayah_id;
            } elseif ($wilayah->tipe == 'KAB') {
                $parents['kabupaten_id'] = $wilayah_id;
            } elseif ($wilayah->tipe == 'PROV') {
                $parents['provinsi_id'] = $wilayah_id;
            }

            return response()->json($parents, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}