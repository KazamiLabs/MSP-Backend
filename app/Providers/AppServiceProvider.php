<?php

namespace App\Providers;

use App\Tools\Ocr\JdWanXiang;
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

        $this->app->singleton(JdWanXiang::class, function ($app) {
            return new JdWanXiang(Config::get('ocr.jdwanxiang.appkey'));
        });

        $this->app->singleton(Requests_Session::class, function ($app) {
            return new Requests_Session('', [], [], [
                'useragent' => 'Kazami-Labs-Auto-Publish-Application',
            ]);
        });

    }
}
