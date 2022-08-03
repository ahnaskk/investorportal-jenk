<?php

namespace App\Console\Commands;

use App\AchRequest;
use App\CompanyAmount;
use App\InvestorTransaction;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantUser;
use App\MNotes;
use App\Models\InvestorAchRequest;
use App\ParticipentPayment;
use App\PaymentPause;
use App\TermPaymentDate;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\VelocityFee;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserActivityLogFixOldData extends Command
{
    // Used for converting ID to Name for old data in user activity log. This can be reused if needed for further modificaion in log.


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'useractivitylog:fixdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes ID to Name.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2000000000M');

        $logs = UserActivityLog::where('type', 'payment')->where('action', 'deleted')->whereNull('investor_id')->whereNull('merchant_id')->get();
        try {
            foreach ($logs as $key => $log) {
                $merchant_id = null;
                $investor_id = null;
                $data = json_decode($log->detail, true);
                if (array_key_exists('merchant_id', $data) && $data['merchant_id']) {
                    $merchant_id = $data['merchant_id'];
                } elseif (array_key_exists('merchant_id', $data) && ($data['merchant_id'] == 0) && array_key_exists('model_id', $data)) {
                    $investor_id = UserActivityLog::where(['object_id' => $data['model_id'], 'type' => 'investor_transaction'])->value('investor_id');
                }
                $log->merchant_id = $merchant_id;
                $log->investor_id = $investor_id;
                $log->save();
                echo "\n Successfully updated useractivitylog data having ID.)".$log->id;
            }
            echo 'successfully fixed data';
        } catch (\Throwable $th) {
            throw $th;
            echo 'Failed to update log';
        }

        return 0;
    }
}
