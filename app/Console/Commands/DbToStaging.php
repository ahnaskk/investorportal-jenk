<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DbToStaging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:staging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy Live DB to Staging';

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
        shell_exec('mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').' -P25060 --set-gtid-purged=OFF '.config('app.database').' > first.sql');
        echo "\n DB Exported from Live";
        shell_exec(' yes | mysqladmin -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060  drop '.config('app.database2'));
        echo "\n Dropped ".config('app.database2');
        shell_exec('mysqladmin -h '.config('app.db_url').'   -u '.config('app.username').' -p'.config('app.password').'  -P25060  create '.config('app.database2'));
        echo "\n  Created ".config('app.database2');
        shell_exec('mysql -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060 '.config('app.database2').' < first.sql');
        echo "\n  Imported to  ".config('app.database2');

        $admin_password = DB::connection('mysql2')->table('users')->where('id', 1)->update(['password' => '$2y$10$k0w4SCNHmNoqV8iTCXHbue8kTRQH8Ubrvw1WkL/ZDldD5FlnCypTS']);
        if ($admin_password) {
            echo "\n Admin password Change - success";
        } else {
            echo "\n Admin password Change - Failed";
        }
        $account_user = DB::connection('mysql2')->update("update users set name = 'accounts' WHERE id = 449");
        if ($account_user) {
            echo "\n Account User Added - success";
        } else {
            echo "\n Account User Added - Failed";
        }
        $account_user = DB::connection('mysql2')->table('users')->where('id', 449)->update(['password' => '$2y$10$0DEkZLQHwG7HBxQCVGaJduvaKQfOrSSKKccsbzssB7U0B8tzEI9tS']);//accounts@iocod
        if ($account_user) {
            echo "\n Account User Added - success";
        } else {
            echo "\n Account User Added - Failed";
        }
        $editor_user = DB::connection('mysql2')->update("update users set name = 'editor' WHERE id = 72");
        if ($editor_user) {
            echo "\n Editor User Added - success";
        } else {
            echo "\n Editor User Added - Failed";
        }
        $editor_user = DB::connection('mysql2')->table('users')->where('id', 72)->update(['password' => '$2y$10$Jlt1lCX6JvE8W60H/krRS.nD6.FSVJYgtupb6jXetQgsBYOUaztXK']);//editor@iocod
        if ($editor_user) {
            echo "\n Editor User Password Changed - success";
        } else {
            echo "\n Editor User Password Changed - Failed";
        }
        $collection_user = DB::connection('mysql2')->update("update users set name = 'collection' WHERE id = 176");
        if ($collection_user) {
            echo "\n collection User Added - success";
        } else {
            echo "\n collection User Added - Failed";
        }
        $collection_user = DB::connection('mysql2')->table('users')->where('id', 176)->update(['password' => '$2y$10$0NcYybtaYGpam0BJR/W6pu/UGpnPtCCT/PnkfvhJ6WTTJSJYyz9TK']);//collection@iocod
        if ($collection_user) {
            echo "\n collection User Password Changed - success";
        } else {
            echo "\n collection User Password Changed - Failed";
        }
        $company_user = DB::connection('mysql2')->update("update users set name = 'company' WHERE id = 58");
        if ($company_user) {
            echo "\n company User Added - success";
        } else {
            echo "\n company User Added - Failed";
        }
        $company_user = DB::connection('mysql2')->table('users')->where('id', 58)->update(['password' => '$2y$10$mBAJoaeGTepPuinqVTQ7zeSIb63aSizCEdrewppNjKitEEafjNWB2']);//company@iocod
        if ($company_user) {
            echo "\n company User Password Changed - success";
        } else {
            echo "\n company User Password Changed - Failed";
        }
        $second_admin_user = DB::connection('mysql2')->update("update users set name = 'admin' WHERE id = 51");
        if ($second_admin_user) {
            echo "\n Second User Added - success";
        } else {
            echo "\n Second User Added - Failed";
        }
        $second_admin_user = DB::connection('mysql2')->table('users')->where('id', 51)->update(['password' => '$2y$10$//NGQrKZLUO4UyW1orsR/uEkp601mhlUSqDSVRH.a06gENv4JJXf']);//admin@iocod
        if ($second_admin_user) {
            echo "\n Second User Password Changed - success";
        } else {
            echo "\n Second User Password Changed - Failed";
        }
        $merchants_name = DB::connection('mysql2')->update('update merchants set name = CONCAT(id,name)');
        if ($merchants_name) {
            echo "\n Merchants name Change - success";
        } else {
            echo "\n Merchants name Change - Failed";
        }
        $merchants_phone = DB::connection('mysql2')->update('update merchants set phone = NULL');
        if ($merchants_phone) {
            echo "\n Merchants phone Change - success";
        } else {
            echo "\n Merchants phone Change - Failed";
        }
        $merchants_cell_phone = DB::connection('mysql2')->update('update merchants set cell_phone = NULL');
        if ($merchants_cell_phone) {
            echo "\n Merchants cell_phone Change - success";
        } else {
            echo "\n Merchants cell_phone Change - Failed";
        }
        $users_name = DB::connection('mysql2')->update('update users set name = CONCAT(id,name)');
        if ($users_name) {
            echo "\n Users name Change - success";
        } else {
            echo "\n Users name Change - Failed";
        }
        $users_email = DB::connection('mysql2')->update("update users set  email = CONCAT(id, 'email@iocod.com')");
        if ($users_email) {
            echo "\n Users email Change - success";
        } else {
            echo "\n Users email Change - Failed";
        }
        $second_admin_user = DB::connection('mysql2')->update("update users set email = 'admin@iocod.com' WHERE id=51");
        if ($second_admin_user) {
            echo "\n Second Admin Email Updated - success";
        } else {
            echo "\n Second Admin Email Updated - Failed";
        }
        $accounts_user = DB::connection('mysql2')->update("update users set email = 'accounts@iocod.com' WHERE id=449");
        if ($accounts_user) {
            echo "\n Accounts Email Updated - success";
        } else {
            echo "\n Accounts Email Updated - Failed";
        }
        $editor_user = DB::connection('mysql2')->update("update users set email = 'editor@iocod.com' WHERE id=72");
        if ($editor_user) {
            echo "\n Editor Email Updated - success";
        } else {
            echo "\n Editor Email Updated - Failed";
        }
        $collection_user = DB::connection('mysql2')->update("update users set email = 'collection@iocod.com' WHERE id=176");
        if ($collection_user) {
            echo "\n collection Email Updated - success";
        } else {
            echo "\n collection Email Updated - Failed";
        }
        $company_user = DB::connection('mysql2')->update("update users set email = 'company@iocod.com' WHERE id=58");
        if ($editor_user) {
            echo "\n company Email Updated - success";
        } else {
            echo "\n company Email Updated - Failed";
        }
        $users_notifictn_email = DB::connection('mysql2')->update("update users set  notification_email = CONCAT(id, 'email@iocod.com')");
        if ($users_notifictn_email) {
            echo "\n Users notification email Change - success";
        } else {
            echo "\n Users notification email Change - Failed";
        }
        $merchants_email = DB::connection('mysql2')->update("update merchants set  email = CONCAT(id, 'merchant@iocod.com')");
        if ($merchants_email) {
            echo "\n merchants email Change - success";
        } else {
            echo "\n merchants email Change - Failed";
        }
        $merchants_notifictn_email = DB::connection('mysql2')->update("update merchants set  notification_email = CONCAT(id, 'merchant@iocod.com')");
        if ($merchants_notifictn_email) {
            echo "\n merchants notification email Change - success";
        } else {
            echo "\n merchants notification email Change - Failed";
        }
        /*$return=Artisan::call('db:seed --class=View');
        if(!$return)
            echo "\n View Taable Created - success";
        else
            echo "\n View Taable Created - Failed";*/
        $message_table = DB::connection('mysql2')->update('update messages set  mobile = NULL');
        if ($message_table) {
            echo "\n messages's Mobile Change To Null - success";
        } else {
            echo "\n messages's Mobile Change To Null - Failed";
        }
        echo "\n Finished";
        Artisan::call('db:export');
        echo "\n Please Download the ZIP now";
    }
}
