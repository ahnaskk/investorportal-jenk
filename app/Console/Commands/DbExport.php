<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Db and create Zip';

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
        $ViewTables = '/home/forge/investorportal.vgusa.com/database/sql/ViewTables.sql';
        shell_exec('cd /home/forge/investorportal.vgusa.com; mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').' -P25060 --set-gtid-purged=OFF '.config('app.database2').' --ignore-table='.config('app.database2').'.activity_log > investorportal2.sql');
        shell_exec('cd /home/forge/investorportal.vgusa.com; cat investorportal2.sql '.$ViewTables.'  > investorportal.sql');
        echo "\n Exported to ".config('app.db_url');

        $zip = '/home/forge/staging.investorportal.vgusa.com/public/investorportal.zip';
        $sql = '/home/forge/investorportal.vgusa.com/investorportal.sql';
        shell_exec('rm -r /home/forge/staging.investorportal.vgusa.com/public/investorportal.zip');
        shell_exec('zip '.$zip.' '.$sql);
        echo "\n Compressed \n";
    }
}
