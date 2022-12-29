<?php

namespace Mybizna\Assets\Providers;

use Illuminate\Support\ServiceProvider;

class MybiznaAssetsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '../mybizna' => public_path('mybizna'),
        ], 'public');
    }
}
