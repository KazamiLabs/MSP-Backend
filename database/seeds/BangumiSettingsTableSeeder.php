<?php

use App\BangumiSetting;
use Illuminate\Database\Seeder;

class BangumiSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(BangumiSetting::class, 10)->create();
    }
}
