<?php

namespace Mybizna\Assets\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
            base_path('vendor/mybizna/assets/src/mybizna') => public_path('mybizna'),
        ], 'laravel-assets');

        $migrationFileName = 'add_fields_in_users_table.php';
        if (!$this->migrationFileExists($migrationFileName)) {
            $this->publishes([
                __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
            ], 'migrations');
        }

    }

    protected function migrationFileExists($mgr)
    {
        $path = database_path('migrations/');
        $files = scandir($path);
        $pos = false;
        foreach ($files as &$value) {
            $pos = strpos($value, $mgr);
            if ($pos !== false) {
                return true;
            }

        }
        return false;
    }

}
