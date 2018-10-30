<?php

namespace App\Providers;

use App\Tool\Ocr\Ruokuai;
use Illuminate\Support\ServiceProvider;

class OcrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(Ruokuai::class, function ($app) {
            return new Ruokuai(config('ruokuai.username'), config('ruokuai.password'));
        });
    }
}
