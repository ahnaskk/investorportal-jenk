<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitiatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

/*		$sql = file_get_contents(database_path() . '/seeds/industries.sql');

        DB::statement($sql);

*/
        $sql = file_get_contents(database_path().'/seeders/seeders.sql');

        DB::unprepared($sql);
    }
}
