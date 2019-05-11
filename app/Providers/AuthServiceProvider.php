<?php

namespace App\Providers;

use App\Auth\TokenGuard;
use App\Auth\UserProvider;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //

        Auth::provider('custom', function () {
            return Container::getInstance()->make(UserProvider::class);
        });

        Auth::extend('token', function ($app, $name, array $config) {
            return Container::getInstance()->make(TokenGuard::class, [
                'provider' => Auth::createUserProvider($config['provider']),
                'request'  => $app->request,
            ]);
        });
    }
}
