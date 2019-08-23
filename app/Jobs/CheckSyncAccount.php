<?php

namespace App\Jobs;

use App\BangumiSetting;
use App\Events\CheckSyncAccountFailed;
use App\Events\CheckSyncAccountSuccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class CheckSyncAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Bangumi Setting
     *
     * @var BangumiSetting
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-08-22
     */
    private $bangumiSetting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BangumiSetting $bangumiSetting)
    {
        //
        $this->bangumiSetting = $bangumiSetting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $setting = $this->bangumiSetting;
        $class   = "\\App\\Drivers\\Bangumi\\{$setting->sitedriver}";

        $driver = App::make($class, [
            'username' => $setting->username,
            'password' => $setting->password,
        ]);

        if ($driver->checkAccount()) {
            $setting->status = BangumiSetting::STATUS_ENABLED;
            App::make('events')->dispatch(new CheckSyncAccountSuccess($setting));
        } else {
            $setting->status = BangumiSetting::STATUS_DISABLED;
            App::make('events')->dispatch(new CheckSyncAccountFailed($setting));
        }

        $setting->save();
    }
}
