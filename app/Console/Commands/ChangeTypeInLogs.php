<?php

namespace App\Console\Commands;

use App\User;
use App\UserActivityLog;
use Illuminate\Console\Command;

class ChangeTypeInLogs extends Command
{

    // single purpose only


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'useractivitylog:change_type {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes Type/Fix type of Lender, Investor in useractivity logs in old data';

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
        $arguments = $this->arguments();
        $user_type = UserActivityLog::where('type', 'user')->get();
        foreach ($user_type as $log) {
            $user  = User::withTrashed()->where('id', $log->object_id)->first();
            if ($user) {
                if ($user->hasRole('investor') and $arguments['type'] == 'investor') {
                    $log->type = 'investor';
                    $log->save();
                } elseif ($user->hasRole('lender') and $arguments['type'] == 'lender') {
                    $log->type = 'lender';
                    $log->save();
                } elseif ($user->hasRole('company') and $arguments['type'] == 'company') {
                    $log->type = 'company';
                    $log->save();
                }
            }
        }
        echo "Successfully fixed types in logs";
        return 0;
    }
}
