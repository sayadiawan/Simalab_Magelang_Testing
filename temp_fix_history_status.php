<?php
// Temporary script to add history status to controller

$file = 'package/masterweb/src/Http/Controllers/LaboratoriumPermohonanUjiKlinikManagement2.php';
$content = file_get_contents($file);

// Find and replace for sub-parameter (around line 5296)
$pattern = '/\$item_permohonan_parameter_subsatuan\[\'hasil_permohonan_uji_sub_parameter_klinik\'\] = \$hasil_sub_parameter;\s*\n\s*\n\s*\$item_permohonan_parameter_subsatuan\[\'flag_permohonan_uji_sub_parameter_klinik\'\] = \$value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;/';
$replacement = "\$item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = \$hasil_sub_parameter;\n                \n                // Add selected_history_id and history count for displaying status\n                \$item_permohonan_parameter_subsatuan['selected_history_id'] = \$value_subsatuan->selected_history_id ?? null;\n                \$history_count_sub = PermohonanUjiSubParameterKlinikHistory::where('permohonan_uji_sub_parameter_klinik_id', \$value_subsatuan->id_permohonan_uji_sub_parameter_klinik)->count();\n                \$item_permohonan_parameter_subsatuan['history_count'] = \$history_count_sub;\n                \$item_permohonan_parameter_subsatuan['has_selected_history'] = !empty(\$value_subsatuan->selected_history_id) && \$history_count_sub > 0;\n                \n                \$item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = \$value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;";

$content = preg_replace($pattern, $replacement, $content, 1);

file_put_contents($file, $content);
echo "Done\n";



