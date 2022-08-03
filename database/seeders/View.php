<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class View extends Seeder
{
    public function run()
    {
        //to Re Create the View Structure;
        $path = base_path('database/sql/ViewTables.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
        $view_table_migration_rows=DB::table('migrations');
        $view_table_migration_rows=$view_table_migration_rows->whereIn('migration', [
            '2022_06_12_050034_create_penny_adjustments_table',
            '2021_05_03_123645_create_payment_vs_investor_diffrence_view',
            '2021_06_10_042814_create_transaction_views_table',
            '2021_10_12_023635_create_manual_liquidity_log_views_table',
            '2021_10_12_024744_create_manual_r_t_r_balance_log_views_table',
            '2021_09_09_104002_create_payment_to_marchant_user_sync_procedures_table',
            '2021_09_09_112748_create_profit_and_principle_update_procedures_table',
            '2021_09_09_115316_create_merchant_investor_participant_share_sum_procedures_table',
            '2021_09_09_115406_create_merchant_investor_principal_sum_procedures_table',
        ]);
        $view_table_migration_rows=$view_table_migration_rows->delete();
        $database_path = database_path();
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2022_06_12_050034_create_penny_adjustments_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_05_03_123645_create_payment_vs_investor_diffrence_view",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_06_10_042814_create_transaction_views_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_10_12_023635_create_manual_liquidity_log_views_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_10_12_024744_create_manual_r_t_r_balance_log_views_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_09_09_104002_create_payment_to_marchant_user_sync_procedures_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_09_09_112748_create_profit_and_principle_update_procedures_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_09_09_115316_create_merchant_investor_participant_share_sum_procedures_table",'--force' => true]);
        Artisan::call('migrate', ['--path' => $database_path."/migrations/2021_09_09_115406_create_merchant_investor_principal_sum_procedures_table",'--force' => true]);
    }
}
