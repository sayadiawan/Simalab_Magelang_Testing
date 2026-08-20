<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParameterCategoryLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['code' => 'A', 'name' => 'HEMATOLOGI', 'width' => 6],
            ['code' => 'B', 'name' => 'URIN', 'width' => 3],
            ['code' => 'C', 'name' => 'IMUNOLOGI', 'width' => 3],
            ['code' => 'D', 'name' => 'KIMIA DARAH', 'width' => 12],
        ];

        foreach ($categories as $index => $category) {
            $categoryId = (string) Str::uuid();
            
            DB::table('ms_param_category_layout')->insert([
                'id_param_category_layout' => $categoryId,
                'category_code' => $category['code'],
                'category_name' => $category['name'],
                'column_width' => $category['width'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
