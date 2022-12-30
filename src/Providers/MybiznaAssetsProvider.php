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
            base_path('vendor/mybizna/assets/src/mybizna') => public_path('mybizna'),
        ], 'laravel-assets');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../views', 'mybizna');

        $DS = DIRECTORY_SEPARATOR;
        $modules_path = realpath(base_path()) . $DS . 'Modules';
        if (is_dir($modules_path)) {
            $modules = [];
            $dir = new \DirectoryIterator($modules_path);

            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $module_name = $fileinfo->getFilename();
                    $modules[] = [$module_name => true];
                }
            }

            $modules_str = json_encode($modules, JSON_PRETTY_PRINT);
            
            $fp = fopen(realpath(base_path()) . $DS . 'modules_statuses.json', 'w');
            fwrite($fp, $modules_str);
            fclose($fp);
        }
    }
}
