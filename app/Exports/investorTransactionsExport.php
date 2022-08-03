<?php

namespace App\Exports;

use App\investor_transactions;
use Maatwebsite\Excel\Concerns\FromCollection;

class investorTransactionsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return investor_transactions::all();
    }
}
