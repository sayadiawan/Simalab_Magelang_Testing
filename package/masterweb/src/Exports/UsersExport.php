<?php

namespace Smt\Masterweb\Exports;


use Smt\Masterweb\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class UsersExport implements FromCollection, WithCustomStartCell
{
    public function collection()
    {
        return User::all();
    }

    public function startCell(): string
    {
        return 'B2';
    }
}
