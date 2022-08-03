<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CommandsController extends Controller
{
    public function git_pull($branch = 'master')
    {
        $process = Process::fromShellCommandline("git pull origin $branch");
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });
    }

    public function export_db()
    {
        shell_exec('mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').' -P25060 --set-gtid-purged=OFF '.config('app.database').' > first.sql');
        shell_exec(' yes | mysqladmin -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060  drop '.config('app.database2'));
        shell_exec('mysqladmin -h '.config('app.db_url').'   -u '.config('app.username').' -p'.config('app.password').'  -P25060  create '.config('app.database2'));
        shell_exec('mysql -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060 '.config('app.database2').' < first.sql');
        $admin_password = DB::connection('mysql2')->table('users')->where('id', 1)->update(['password' => '$2y$10$k0w4SCNHmNoqV8iTCXHbue8kTRQH8Ubrvw1WkL/ZDldD5FlnCypTS']);
        if ($admin_password) {
            echo "\n Admin password Change - success";
        } else {
            echo "\n Admin password Change - Failed";
        }
        $merchants_name = DB::connection('mysql2')->update("update merchants set name = CONCAT(id,'merchant')");
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
        $users_name = DB::connection('mysql2')->update("update users set name = CONCAT(id,'name')");
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
        $return = Artisan::call('db:seed --class=View');
        if (! $return) {
            echo "\n View Taable Created - success";
        } else {
            echo "\n View Taable Created - Failed";
        }
        echo "\n Finished";
        shell_exec('mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060  '.config('app.database2').' > investorportal.sql');
        shell_exec('zip investorportal.zip investorportal.sql');

        return redirect()->to('/investorportal.zip');
        echo 'imported';
    }

    public function tst()
    {
        echo 'mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').' '.config('app.database').' > first.sql';
    }
}
