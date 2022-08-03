<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class Data_arrExport implements FromArray
{
    protected $data_arr;

    public function __construct(array $data_arr)
    {
        $this->data_arr = $data_arr;
    }

    public function array(): array
    {
        return $this->data_arr;
    }
}
