<?php

namespace App\Console\Commands;

use App\InvestorTransaction;
use Illuminate\Console\Command;

class TransactionMethodModify extends Command
{
    protected $signature = 'transactionmethod:update';
    protected $description = 'Update Transaction Method Based on Transaction Type';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $InvestorTransaction = InvestorTransaction::get();
        foreach ($InvestorTransaction as $key => $value) {
            if ($value->transaction_type == InvestorTransaction::DEBIT) {
                $transaction_method = InvestorTransaction::MethodByAdminDebit;
            } else {
                $transaction_method = InvestorTransaction::MethodByAdminCredit;
            }
            $value->transaction_method = $transaction_method;
            $value->save();
        }
    }
}
