<?php


    namespace Smt\Masterweb\Imports;

    use Illuminate\Support\Collection;
    use Maatwebsite\Excel\Concerns\ToCollection;

    use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

    class FormImport implements ToCollection, WithCalculatedFormulas
    {
        /**
        * @param array $row
        *
        * @return \Illuminate\Database\Eloquent\Model|null
        */
        public function collection(Collection $rows)
        {

        }
    }

?>
