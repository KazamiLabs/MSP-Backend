<?php

use Illuminate\Database\Seeder;

class CrontabsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $prev_memory_usage = memory_get_usage();
        $crontabs = factory(App\Crontab::class, 1);
        $crontabs->create();
        // unset($crontabs);
        // echo 'GC Collect Cycles:', gc_collect_cycles(), PHP_EOL;
        // $after_memory_usage = memory_get_usage();
        // echo 'Memory diff:', $after_memory_usage - $prev_memory_usage, 'Byte(s)', PHP_EOL;
    }
}
