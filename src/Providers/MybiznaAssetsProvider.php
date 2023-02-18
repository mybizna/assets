<?php

namespace Mybizna\Assets\Providers;

use App\Models\User;
use Artisan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Mybizna\Automigrator\Commands\MigrateCommand;
use Modules\Base\Classes\Datasetter;

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
            Config::set('database.connections.mysql.database', DB_NAME);
            Config::set('database.connections.mysql.username', DB_USER);
            Config::set('database.connections.mysql.password', DB_PASSWORD);
            Config::set('database.connections.mysql.host', DB_HOST);
        }

        $this->publishes([
            base_path('vendor/mybizna/assets/src/mybizna') => public_path('mybizna'),
        ], 'laravel-assets');

        $migrationFileName = 'add_username_field_in_users_table.php';
        if (!$this->migrationFileExists($migrationFileName)) {
            $this->publishes([
                __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
            ], 'migrations');
        }

        $this->moduleComponents();

        $this->initializeConfig();

        if (!App::runningInConsole()) {
            // app is running in console
            $this->processModule();
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
    private function moduleComponents()
    {

        $DS = DIRECTORY_SEPARATOR;

        $modules_path = realpath(base_path()) . $DS . 'Modules';

        if (is_dir($modules_path)) {
            $dir = new \DirectoryIterator($modules_path);
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $module_name = $fileinfo->getFilename();
                    $module_folder = $modules_path . $DS . $module_name . $DS . 'views';
                    if (File::isDirectory($module_folder)) {
                        $this->publishes([
                            base_path('Modules/' . $module_name . '/views') => public_path('mybizna/assets/' . Str::lower($module_name)),
                        ], 'laravel-assets');
                    }

                }
            }
        }

    }

    private function initializeConfig()
    {
        $logging_config = $this->app['config']->get('logging', []);
        $logging_config['channels']['datasetter'] = [
            'driver' => 'single',
            'path' => storage_path('logs/datasetter.log'),
        ];
        $this->app['config']->set('logging', $logging_config);

    }

    private function processModule()
    {
        $realpath = realpath(base_path());
        $migrate_command = new MigrateCommand();
        $datasetter = new Datasetter();

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
            $this->saveFile($realpath . $DS . 'versions.json', $new_versions);
        }

        if ($need_migration) {
            Artisan::call('cache:table');
            Artisan::call('session:table');
            Artisan::call('migrate');
            $migrate_command->migrateModels(true);
            $this->initiateUser();
            $datasetter->dataProcess();
        }

    }

    private function initiateUser()
    {
        $userCount = User::count();

        if (!$userCount) {

            $user_cls = new User();

            if (defined('MYBIZNA_USER_LIST')) {
                $wp_user_list = MYBIZNA_USER_LIST;
                foreach ($wp_user_list as $key => $wp_user) {
                    $user_cls->password = Hash::make(uniqid());
                    $user_cls->email = $wp_user->user_email;
                    $user_cls->name = $wp_user->user_nicename;
                    $user_cls->save();
                }

            } else {

                $user_cls->password = Hash::make('admin');
                $user_cls->email = 'admin@admin.com';
                $user_cls->name = 'Admin User';
                $user_cls->save();
            }
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

        try {
            touch($path);
            chmod($path, 0775);
            $fp = fopen($path, 'w');
            fwrite($fp, $modules_str);
            fclose($fp);
        } catch (\Throwable$th) {
            //throw $th;
        }
    }
}
