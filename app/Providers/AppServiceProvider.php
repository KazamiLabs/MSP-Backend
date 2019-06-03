<?php

namespace App\Providers;

use App\Tools\Ocr\JdWanXiang\JdWanXiang;
use App\Tools\Ocr\JdWanXiang\Showapi;
use App\Tools\Ocr\Ruokuai;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Requests_Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(Ruokuai::class, function ($app) {
            return new Ruokuai(Config::get('ocr.ruokuai.username'), Config::get('ocr.ruokuai.password'));
        });

        $this->app->bind(JdWanXiang::class, function ($app) {
            return $app->make(Showapi::class, ['appkey' => Config::get('ocr.jdwanxiang.appkey')]);
        });

        $this->app->singleton(Requests_Session::class, function ($app) {
            return new Requests_Session('', [], [], [
                'useragent' => 'Kazami-Labs-Auto-Publish-Application',
            ]);
        });

    }
}
