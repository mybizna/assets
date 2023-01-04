<?php

namespace Mybizna\Assets\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Legodion\Lucid\Commands\MigrateCommand;
use Artisan;

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

        if (DB_NAME) {
            Config::set('database.connections.mysql.database', DB_NAME);
            Config::set('database.connections.mysql.username', DB_USER);
            Config::set('database.connections.mysql.password', DB_PASSWORD);
            Config::set('database.connections.mysql.host', DB_HOST);
        }

        $this->publishes([
            base_path('vendor/mybizna/assets/src/mybizna') => public_path('mybizna'),
        ], 'laravel-assets');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../views', 'mybizna');

        

        $this->processModule();
    }

    private function processModule()
    {
        $realpath = realpath(base_path());
        $migrate_command = new MigrateCommand();

        $DS = DIRECTORY_SEPARATOR;
        $modules_path = $realpath . $DS . 'Modules';

        if (is_dir($modules_path)) {

            $modules = [];
            $new_versions = [];
            $need_migration = false;
            $versions = $this->getVersions();

            $dir = new \DirectoryIterator($modules_path);

            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $module_name = $fileinfo->getFilename();

                    $composer = $this->getComposer($module_name);

                    if (!isset($versions[$module_name]) || $versions[$module_name] != $composer['version']) {
                        $need_migration = true;
                    }

                    $modules[$module_name] = true;
                    $new_versions[$module_name] = $composer['version'];
                }
            }
            ksort($modules);
            ksort($new_versions);
            $this->saveFile($realpath . $DS . 'modules_statuses.json', $modules);
            $this->saveFile($realpath . $DS . 'versions.json', ksort($new_versions));
        }

        if ($need_migration) {
            Artisan::call('migrate');
            $migrate_command->migrateModels(true);
        }
    }

    private function getVersions()
    {

        $DS = DIRECTORY_SEPARATOR;

        $path = realpath(base_path()) . $DS . 'versions.json';
        if (file_exists($path)) {

            $json = file_get_contents($path);

            return json_decode($json, true);
        }
        return [];
    }

    private function getComposer($module_name)
    {
        $DS = DIRECTORY_SEPARATOR;

        $path = realpath(base_path()) . $DS . 'Modules' . $DS . $module_name . $DS . 'composer.json';

        $json = file_get_contents($path);

        return json_decode($json, true);
    }

    private function saveFile($path, $data)
    {
        $modules_str = json_encode($data, JSON_PRETTY_PRINT);

        touch($path);
        chmod($path, 0775);
        $fp = fopen($path, 'w');
        fwrite($fp, $modules_str);
        fclose($fp);}
}
