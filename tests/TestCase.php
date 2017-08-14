<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->register(\Jenssegers\Mongodb\MongodbServiceProvider::class);
        $app->register('Encore\Admin\Providers\AdminServiceProvider');

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('app.path.storage', __DIR__."/../files");
        $this->app['config']->set('app.path.public', __DIR__."/../files/public");
        $this->app['config']->set('database.default', 'mongodb');
        $this->app['config']->set('database.connections.mongodb.host', 'localhost');
        $this->app['config']->set('database.connections.mongodb.port', '27017');
        $this->app['config']->set('database.connections.mongodb.database', 'mongodb_test');
        $this->app['config']->set('database.connections.mongodb.username', '');
        $this->app['config']->set('database.connections.mongodb.password', '');
        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('filesystems', require __DIR__.'/config/filesystems.php');
        $this->app['config']->set('admin', require __DIR__.'/config/admin.php');

        $this->artisan('vendor:publish');

        //$this->migrate();

        $this->artisan('admin:install');

        \Encore\Admin\Facades\Admin::registerAuthRoutes();

        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        require __DIR__.'/routes.php';

        require __DIR__.'/seeds/factory.php';
    }

    public function tearDown()
    {
        //$this->rollback();

        Schema::drop('test_images');
        Schema::drop('test_multiple_images');
        Schema::drop('test_files');
        Schema::drop('test_users');
        Schema::drop('test_user_profiles');
        Schema::drop('test_tags');
        Schema::drop('test_user_tags');
        Schema::drop('admin_users');
        Schema::drop('admin_roles');
        Schema::drop('admin_permissions');
        Schema::drop('admin_menu');
        Schema::drop('admin_user_permissions');
        Schema::drop('admin_role_users');
        Schema::drop('admin_role_permissions');
        Schema::drop('admin_role_menu');
        Schema::drop('admin_operation_log');

        parent::tearDown();
    }

    /**
     * run package database migrations.
     *
     * @return void
     */
    public function migrate()
    {
        foreach ($this->getMigrations() as $migration) {
            (new $migration())->up();
        }
    }

    public function rollback()
    {
        foreach ($this->getMigrations() as $migration) {
            (new $migration())->down();
        }
    }

    protected function getMigrations()
    {
        $migrations = [];

        $fileSystem = new Filesystem();

        foreach ($fileSystem->files(__DIR__.'/../migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrations[] = $this->getMigrationClass($file);
        }

        foreach ($fileSystem->files(__DIR__.'/migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrations[] = $this->getMigrationClass($file);
        }

        return $migrations;
    }

    protected function getMigrationClass($file)
    {
        $file = str_replace('.php', '', basename($file));

        $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));

        return $class;
    }
}
