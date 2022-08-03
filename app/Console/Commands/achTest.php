<?php

namespace App\Console\Commands;

use App\AchRequest;
use App\Http\Controllers\Admin\PaymentController;
use App\Rcode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PaymentHelper;

class achTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'localdb:achtest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentController $payment)
    {
        $this->payment = $payment;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication success. \n";
            $rcodes = Rcode::pluck('description', 'id')->toArray();
            $requests = AchRequest::where('ach_status', 0)->orderByDesc('created_at')->take(5)->get();
            $responses = [
                ['curr_bill_status' =>'Settled'],
                ['curr_bill_status' =>'Settled'],
                ['curr_bill_status' =>'Returned - Payment Stopped'],
                ['curr_bill_status' => 'Returned - Account Closed'],
                ['curr_bill_status' => 'Returned - Corporate Customer Advises Not Authorized'],
            ];
            foreach ($requests as $key => $req) {
                echo " ACH checking , ACH ID=$req->id \n";

                if ($req->ach_request_status == 1 && $req->order_id) {
                    $type = 'Payment Debit';
                    if ($req->is_fees) {
                        $type = 'Fee Debit';
                    }
                    $response = $responses[$key];
                    if (Str::contains($response['curr_bill_status'], 'Settled')) {
                        $participant_amount = $req->payment_amount;
                        if ($req->is_fees) {
                            $velocity_fees = $req->velocityFees();
                            if ($velocity_fees->count()) {
                                $velocity_fees->update(['status' => 1]);
                            }
                        } else {
                            $add_payment = PaymentHelper::generateAchPayment($req->merchant_id, $req->payment_date, $participant_amount, null);
                            $req->schedule()->update(['status' => 1]);
                            $term = $req->schedule->paymentTerm;
                            $term->actual_payment_left -= 1;
                            $term->update();
                        }

                        $req->update(['ach_status' => 1, 'payment_status' => 1]);
                        $status = 'Settled';
                    } elseif (Str::contains($response['curr_bill_status'], 'Returned')) {
                        $status = 'Returned';

                        $rcode = 35;
                        $response_rcode = explode('-', $response['curr_bill_status'], 2);
                        if ($response_rcode[1]) {
                            $rcode = array_search(trim($response_rcode[1]), $rcodes);
                            if ($rcode == false) {
                                if (preg_match('#\((.*?)\)#', $response_rcode[1], $match)) {
                                    $rcode = Rcode::where('code', $match[1])->value('id');
                                    if (! $rcode) {
                                        $rcode = 35;
                                    }
                                } else {
                                    $rcode = 35;
                                }
                            }
                        }

                        if ($req->is_fees) {
                            if ($req->velocityFees->count()) {
                                $req->velocityFees()->update(['status' => -1]);
                            }
                        } else {
                            if ($rcode) {
                                $add_payment = PaymentHelper::generateAchPayment($req->merchant_id, $req->payment_date, 0, $rcode);
                            }

                            $req->schedule()->update(['status' => -1]);
                        }

                        $req->update(['ach_status' => -1, 'payment_status' => -1]);
                    }
                    echo "ACH $type $status, ACH ID=$req->id , merchant_id=$req->merchant_id,amount=$req->payment_amount \n";
                }
            }
            Auth::logout();
            echo "\n".count($requests)." ACH  test status added successfully \n";

            return true;
        } else {
            echo "\n Authentication failed. \n";

            return false;
        }
    }
}
