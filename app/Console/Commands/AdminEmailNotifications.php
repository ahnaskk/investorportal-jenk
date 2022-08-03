<?php

//Important.

namespace App\Console\Commands;

use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AdminEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AdminEmailNotifications:adminemailnotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Maturity date based admin email notifications';

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
     * @return mixed
     */
    public function handle()
    {
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $before_date = date('Y-m-d', strtotime('-4 months'));
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';

        $result = InvestorTransaction::select(
            'investor_id',
            'maturity_date',
            'users.name as investor_name',
            'users.notification_email',
            'transaction_type',
            'amount',
            'date'
        )->whereDate('maturity_date', '<=', $before_date)
                   ->leftJoin('users', 'users.id', 'investor_transactions.investor_id')
                   ->orderByDesc('maturity_date')
                   ->get();

        if (! empty($result)) {
            foreach ($result as $key => $value) {
                if ($value->maturity_date) {
                    $n_url = url('/admin/investors/portfolio/'.$value->investor_id);

                    // $before_date = date("Y-m-d",strtotime($value->maturity_date.' -4 months'));
                    // $value->investor_id;
                    //$before_date = date("Y-m-d",strtotime('-4 months'));
                    $transactionType = ($value->transaction_type == 1) ? 'Debit' : 'Credit';
                    $message['title'] = ' Maturity Date Based on investor '.$value->investor_name;
                    $message['content'] = "Hello Admin!  \r\n  Investor Name : <a href=".$n_url.'>'.$value->investor_name."</a>  \r\n Transaction type : ".$transactionType."  \r\n Amount : ".$value->amount." \r\n Maturity Date :".$value->maturity_date." \r\n Created date :".$value->date;
                    if ($value->notification_email) {
                        try {
                            // need to change queue based mail send here

                            foreach ($emailArray as $email) {
                                //old code
                                // Mail::send('emails.adminnote', ['title' => $message['title'], 'content' => $message['content']], function ($q) use ($value, $email) {
                                //     $q->from('us@example.com', 'Maturity date based');
                                //     $q->to($email)->subject('Maturity date based investor'.$value->investor_name);
                                // });
                                $msg['title'] = $message['title'];
                                $msg['content'] = $message['content'];
                                $msg['to_mail'] = $email;
                                $msg['status'] = 'admin_note';
                                $msg['subject'] = 'Maturity date based investor'.$value->investor_name;
                                $msg['unqID'] = unqID();
                                try {
                                    $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                    dispatch($emailJob);
                                    $msg['to_mail'] = $admin_email;
                                    $emailJob = (new CommonJobs($msg));
                                    dispatch($emailJob);
                                } catch (\Exception $e) {
                                    echo $e->getMessage();
                                }
                            }   // end for each
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    } else {
                        echo "No notification email for investors \n";
                    }
                } else {
                    echo "No maturity date \n";
                }
            }
        }
    }
}
