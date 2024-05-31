<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Illuminate\Contracts\Container\BindingResolutionException;

class SocialiteIVAOServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot()
    {
        $socialite = $this->app->make(Factory::class);

        $socialite->extend('ivao', function () use ($socialite) {
            $config = config('services.ivao');

            return $socialite->buildProvider(SocialiteIVAOProvider::class, $config);
        });
    }
}