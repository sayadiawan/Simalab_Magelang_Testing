<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\ParameterCategoryLayout;
use Smt\Masterweb\Models\ParameterCategoryItem;
use Smt\Masterweb\Models\ParameterPaketKlinik;

class PopulateCategoryItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populate category items based on parameter names
     *
     * @return void
     */
    public function run()
    {
        // Clear existing items first
        DB::table('ms_param_category_items')->delete();

        $categories = ParameterCategoryLayout::where('is_active', true)->get();
        echo "Found " . count($categories) . " categories\n";
        
        // Get all paket parameters
        $allPakets = ParameterPaketKlinik::whereNull('deleted_at')->orderBy('name_parameter_paket_klinik', 'asc')->get();
        echo "Found " . count($allPakets) . " pakets\n\n";

        foreach ($categories as $category) {
            echo "Processing category: {$category->category_name}\n";
            $categoryName = strtolower($category->category_name);
            $sortOrder = 1;

            foreach ($allPakets as $paket) {
                $paketName = strtolower($paket->name_parameter_paket_klinik);
                $shouldAdd = false;

                // HEMATOLOGI
                if (str_contains($categoryName, 'hematologi')) {
                    if (str_contains($paketName, 'darah rutin') ||
                        str_contains($paketName, 'hematologi') ||
                        str_contains($paketName, 'hemoglobin') ||
                        str_contains($paketName, 'hb ') ||
                        str_contains($paketName, 'led') ||
                        str_contains($paketName, 'leko') ||
                        str_contains($paketName, 'diferensial') ||
                        str_contains($paketName, 'golongan darah') ||
                        str_contains($paketName, 'paket haji')) {
                        $shouldAdd = true;
                    }
                }

                // URIN
                if (str_contains($categoryName, 'urin')) {
                    if (str_contains($paketName, 'urin') ||
                        str_contains($paketName, 'urine') ||
                        str_contains($paketName, 'glukosa urin') ||
                        str_contains($paketName, 'protein urin') ||
                        str_contains($paketName, 'sedimen') ||
                        str_contains($paketName, 'narkoba') ||
                        str_contains($paketName, 'kehamilan')) {
                        $shouldAdd = true;
                    }
                }

                // IMUNOLOGI
                if (str_contains($categoryName, 'imunologi') || str_contains($categoryName, 'imunoserologi')) {
                    if (str_contains($paketName, 'widal') ||
                        str_contains($paketName, 'hbsag') ||
                        str_contains($paketName, 'dengue') ||
                        str_contains($paketName, 'igg') ||
                        str_contains($paketName, 'igm') ||
                        str_contains($paketName, 'golongan darah') ||
                        str_contains($paketName, 'pp tes') ||
                        str_contains($paketName, 'antigen')) {
                        $shouldAdd = true;
                    }
                }

                // KIMIA DARAH
                if (str_contains($categoryName, 'kimia')) {
                    if (str_contains($paketName, 'gula') ||
                        str_contains($paketName, 'kolesterol') ||
                        str_contains($paketName, 'hdl') ||
                        str_contains($paketName, 'ldl') ||
                        str_contains($paketName, 'trigliserid') ||
                        str_contains($paketName, 'sgot') ||
                        str_contains($paketName, 'sgpt') ||
                        str_contains($paketName, 'ureum') ||
                        str_contains($paketName, 'kreatinin') ||
                        str_contains($paketName, 'asam urat') ||
                        str_contains($paketName, 'hba1c') ||
                        str_contains($paketName, 'kimia')) {
                        $shouldAdd = true;
                    }
                }

                if ($shouldAdd) {
                    try {
                        $item = new ParameterCategoryItem();
                        $item->id_param_category_layout = $category->id_param_category_layout;
                        $item->id_parameter_paket_klinik = $paket->id_parameter_paket_klinik;
                        $item->sort_order = $sortOrder;
                        $saved = $item->save();
                        
                        if ($saved) {
                            $sortOrder++;
                            echo "✓ Added: {$paket->name_parameter_paket_klinik} to {$category->category_name} (sort: {$item->sort_order})\n";
                        } else {
                            echo "✗ Failed to save: {$paket->name_parameter_paket_klinik}\n";
                        }
                    } catch (\Exception $e) {
                        echo "✗ Error: {$paket->name_parameter_paket_klinik} - " . $e->getMessage() . "\n";
                    }
                }
            }
        }

        echo "\nCategory items populated successfully!\n";
    }
}
