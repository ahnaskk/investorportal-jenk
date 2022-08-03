<?php

namespace App\Console\Commands;

use App\MerchantUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class OverpaymentAccountAdd extends Command
{
    protected $signature = 'OverpaymentAccount:Add';
    protected $description = 'To Add Overpayment Account To existing Merchant User';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $userId = 1;
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        $MerchantUsers = MerchantUser::pluck('merchant_id', 'merchant_id');
        foreach ($MerchantUsers as $merchant_id) {
            $MerchantUser = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $OverpaymentAccount->id)->first();
            if (! $MerchantUser) {
                $item = [
                    'user_id'                   =>$OverpaymentAccount->id,
                    'amount'                    =>0,
                    'merchant_id'               =>$merchant_id,
                    'status'                    =>1,
                    'invest_rtr'                =>0,
                    'mgmnt_fee'                 =>0,
                    'syndication_fee_percentage'=>0,
                    'commission_amount'         =>0,
                    'commission_per'            =>0,
                    'under_writing_fee'         =>0,
                    'under_writing_fee_per'     =>0,
                    'creator_id'                =>$userId,
                    'pre_paid'                  =>0,
                    's_prepaid_status'          =>1,
                    'creator_id'                =>$userId,
                ];
                MerchantUser::create($item);
            }
        }
    }
}
