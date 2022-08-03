<?php

namespace App\Console\Commands\SingleUse;

use App\CompanyAmount;
use App\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompanyAmountChange extends Command
{
    protected $signature = 'change:companyAmount';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::beginTransaction();
        $Merchant = Merchant::get();
        foreach ($Merchant as $key => $value) {
            echo ++$key.')'.$value->id."\n";
            $CompanyAmount = CompanyAmount::
            where('merchant_id', $value->id)
            ->get();
            foreach ($CompanyAmount as $key => $value) {
                $value->max_participant = round($value->max_participant, 2);
                $value->save();
            }
        }
        DB::commit();

        return 0;
    }
}
