<?php

namespace Mybizna\Assets\Providers;

use Config;
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
        if (defined('DB_NAME')) {
            Config::set('app.key', MYBIZNA_APPKEY);
            Config::set('database.connections.mysql.database', DB_NAME);
            Config::set('database.connections.mysql.username', DB_USER);
            Config::set('database.connections.mysql.password', DB_PASSWORD);
            Config::set('database.connections.mysql.host', DB_HOST);
        }

        $this->publishes([
            base_path('vendor/mybizna/assets/src/mybizna') => public_path('mybizna'),
        ], 'laravel-assets');

        $migrationFileName = 'add_fields_in_users_table';
        if (!$this->migrationFileExists($migrationFileName)) {
            $this->publishes([
                __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName . '.php'),
            ], 'migrations');
        }

    }

    protected function migrationFileExists($mgr)
    {
        $path = database_path('migrations/');

        // Check if if $path is a directory
        if (!is_dir($path)) {
            return false;
        }

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
