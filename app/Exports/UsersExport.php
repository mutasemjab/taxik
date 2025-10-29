<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;


class UsersExport implements FromCollection, WithHeadings
{
    /**
     * Return a collection of users for export.
     */
    public function collection()
    {
        return User::join('clas', 'users.clas_id', '=', 'clas.id')
            ->select(
                'users.name',
                'users.identity_number',
                'clas.id as clas_id',
                'clas.name as clas_name',
            )->get();
    }

    /**
     * Specify headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'Name', 
            'identity_number', 
            'Class ID',
            'Class Name',
        ];
    }
}



