<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class dbSetupForLocal extends Command
{
    protected $signature = 'localdb:setup';
    protected $description = 'Setup DB for local by changing merchants and investors name and email and changing admin password';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (config('app.env') == 'local') {
            $path = base_path('database/sql/ViewTables.sql');
            $sql = file_get_contents($path);
            DB::unprepared($sql);
            $admin_password = DB::table('users')->where('id', 1)->update(['password' => '$2y$10$k0w4SCNHmNoqV8iTCXHbue8kTRQH8Ubrvw1WkL/ZDldD5FlnCypTS']);
            if ($admin_password) {
                echo "\n Admin password Change - success";
            } else {
                echo "\n Admin password Change - Failed";
            }
            $account_user = DB::table('users')->where('id', 449)->update(['name'=>'accounts', 'password' => '$2y$10$0DEkZLQHwG7HBxQCVGaJduvaKQfOrSSKKccsbzssB7U0B8tzEI9tS']); //accounts@iocod
            if ($account_user) {
                echo "\n Account User Added - success";
            } else {
                echo "\n Account User Added - Failed";
            }
            $editor_user = DB::table('users')->where('id', 72)->update(['name'=>'editor', 'password' => '$2y$10$Jlt1lCX6JvE8W60H/krRS.nD6.FSVJYgtupb6jXetQgsBYOUaztXK']); //editor@iocod
            if ($editor_user) {
                echo "\n Editor User Added - success";
            } else {
                echo "\n Editor User Added - Failed";
            }
            $collection_user = DB::table('users')->where('id', 176)->update(['name'=>'collection', 'password' => '$2y$10$0NcYybtaYGpam0BJR/W6pu/UGpnPtCCT/PnkfvhJ6WTTJSJYyz9TK']); //collection@iocod
            if ($collection_user) {
                echo "\n collection User Added - success";
            } else {
                echo "\n collection User Added - Failed";
            }
            $company_user = DB::table('users')->where('id', 58)->update(['name'=>'company', 'password' => '$2y$10$mBAJoaeGTepPuinqVTQ7zeSIb63aSizCEdrewppNjKitEEafjNWB2']); //company@iocod
            if ($company_user) {
                echo "\n company User Added - success";
            } else {
                echo "\n company User Added - Failed";
            }
            $second_admin_user = DB::table('users')->where('id', 51)->update(['name'=>'admin', 'password' => '$2y$10$//NGQrKZLUO4UyW1orsR/uEkp601mhlUSqDSVRH.a06gENv4JJXf.']); //admin@iocod
            if ($second_admin_user) {
                echo "\n Second User Added - success";
            } else {
                echo "\n Second User Added - Failed";
            }
            $merchants_name = DB::update('update merchants set name = CONCAT(id,\'MMMM\'), business_en_name = merchants.name, first_name = \'FFF\', last_name = \'LLL\'');
            if ($merchants_name) {
                echo "\n Merchants name Change - success";
            } else {
                echo "\n Merchants name Change - Failed";
            }
            $merchants_phone = DB::update('update merchants set phone = NULL');
            if ($merchants_phone) {
                echo "\n Merchants phone Change - success";
            } else {
                echo "\n Merchants phone Change - Failed";
            }
            $merchants_cell_phone = DB::update('update merchants set cell_phone = NULL');
            if ($merchants_cell_phone) {
                echo "\n Merchants cell_phone Change - success";
            } else {
                echo "\n Merchants cell_phone Change - Failed";
            }
            $users_name = DB::update('update users set name = CONCAT(id,\'XXX\')');
            if ($users_name) {
                echo "\n Users name Change - success";
            } else {
                echo "\n Users name Change - Failed";
            }
            $users_email = DB::update("update users set  email = CONCAT(id, 'email@iocod.com')");
            if ($users_email) {
                echo "\n Users email Change - success";
            } else {
                echo "\n Users email Change - Failed";
            }
            $admin_user_email = DB::table('users')->where('id', 1)->update(['email'=>'1email.33433@iocod.com']);
            if ($admin_user_email) {
                echo "\n Admin Email Updated - success";
            } else {
                echo "\n Admin Email Updated - Failed";
            }
            $second_admin_user = DB::table('users')->where('id', 51)->update(['email'=>'admin@iocod.com']);
            if ($second_admin_user) {
                echo "\n Second Admin Email Updated - success";
            } else {
                echo "\n Second Admin Email Updated - Failed";
            }
            $accounts_user = DB::table('users')->where('id', 449)->update(['email'=>'accounts@iocod.com']);
            if ($accounts_user) {
                echo "\n Accounts Email Updated - success";
            } else {
                echo "\n Accounts Email Updated - Failed";
            }
            $editor_user = DB::table('users')->where('id', 72)->update(['email'=>'editor@iocod.com']);
            if ($editor_user) {
                echo "\n Editor Email Updated - success";
            } else {
                echo "\n Editor Email Updated - Failed";
            }
            $collection_user = DB::table('users')->where('id', 176)->update(['email'=>'collection@iocod.com']);
            if ($collection_user) {
                echo "\n collection Email Updated - success";
            } else {
                echo "\n collection Email Updated - Failed";
            }
            $company_user = DB::table('users')->where('id', 58)->update(['email'=>'company@iocod.com']);
            if ($editor_user) {
                echo "\n company Email Updated - success";
            } else {
                echo "\n company Email Updated - Failed";
            }
            $users_notifictn_email = DB::update("update users set  notification_email = CONCAT(id, 'email@iocod.com')");
            if ($users_notifictn_email) {
                echo "\n Users notification email Change - success";
            } else {
                echo "\n Users notification email Change - Failed";
            }
            $merchants_email = DB::update("update merchants set  email = CONCAT(id, 'merchant@iocod.com')");
            if ($merchants_email) {
                echo "\n merchants email Change - success";
            } else {
                echo "\n merchants email Change - Failed";
            }
            $merchants_notifictn_email = DB::update("update merchants set  notification_email = CONCAT(id, 'merchant@iocod.com')");
            if ($merchants_notifictn_email) {
                echo "\n merchants notification email Change - success";
            } else {
                echo "\n merchants notification email Change - Failed";
            }
            $return = Artisan::call('db:seed --class=View');
            if (! $return) {
                echo "\n View Taable Created - success";
            } else {
                echo "\n View Taable Created - Failed";
            }
            $message_table = DB::update('update messages set  mobile = NULL');
            if ($message_table) {
                echo "\n messages's Mobile Change To Null - success";
            } else {
                echo "\n messages's Mobile Change To Null - Failed";
            }
            $users_phone = DB::update('update users set phone = NULL');
            if ($users_phone) {
                echo "\n Users phone Change - success";
            } else {
                echo "\n Users phone Change - Failed";
            }
            $users_cell_phone = DB::update('update users set cell_phone = NULL');
            if ($users_cell_phone) {
                echo "\n Users cell_phone Change - success";
            } else {
                echo "\n Users cell_phone Change - Failed";
            }
            $admin_email = DB::update("update settings set email = '1email.33433@iocod.com' where id = 1");
            if ($admin_email) {
                echo "\n Admin emails Change - success";
            } else {
                echo "\n Admin emails Change - Failed";
            }
            $delete_statement = DB::table('statements')->delete();
            if ($delete_statement) {
                echo "\n Statements table deleted - success";
            } else {
                echo "\n Statements table deleted - Failed";
            }
            $delete_merchant_statement = DB::table('merchant_statements')->delete();
            if ($delete_merchant_statement) {
                echo "\n Merchant statements table deleted - success";
            } else {
                echo "\n Merchant statements table deleted - Failed";
            }
            $delete_documents = DB::table('documents')->delete();
            if ($delete_documents) {
                echo "\n Merchant documents table deleted - success";
            } else {
                echo "\n Merchant documents table deleted - Failed";
            }
            $delete_investor_documents = DB::table('investor_documents')->delete();
            if ($delete_investor_documents) {
                echo "\n Investor documents table deleted - success";
            } else {
                echo "\n Investor documents table deleted - Failed";
            }
            DB::unprepared('SET foreign_key_checks = 0;');
            $delete_investor_bank_accounts = DB::table('bank_details')->delete();
            if ($delete_investor_bank_accounts) {
                echo "\n Investor bank accounts table deleted - success";
            } else {
                echo "\n Investor bank accounts table deleted - Failed";
            }
            DB::unprepared('SET foreign_key_checks = 1;');
            $delete_merchant_bank_accounts = DB::table('merchant_bank_accounts')->delete();
            if ($delete_merchant_bank_accounts) {
                echo "\n Merchant bank accounts table deleted - success";
            } else {
                echo "\n Merchant bank accounts table deleted - Failed";
            }
            $update_merchants_ach_order_id = DB::table('ach_requests')->update([
                'order_id' => DB::raw('FLOOR(11111111 + (RAND() * 88888888))')
            ]);
            if ($update_merchants_ach_order_id) {
                echo "\n Merchant ACH table updated - success";
            } else {
                echo "\n Merchant ACH table updated - Failed";
            }
            $update_investors_ach_order_id = DB::table('investor_ach_requests')->update([
                'order_id' => DB::raw('FLOOR(11111111 + (RAND() * 88888888))')
            ]);
            if ($update_investors_ach_order_id) {
                echo "\n Investors ACH table updated - success";
            } else {
                echo "\n Investors ACH table updated - Failed";
            }
            echo "\n Finished";
        } else {
            echo 'Not In local Mode Please Change it to local';
        }
    }
}
