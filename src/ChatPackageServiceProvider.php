<?php

namespace ChatPackage\ChatPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ChatPackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/config/chat-package.php', 'chat-package'
        );

        // Bind repository interfaces to implementations (Dependency Inversion Principle)
        $this->app->bind(
            \ChatPackage\ChatPackage\Repositories\Contracts\ChatRoomRepositoryInterface::class,
            \ChatPackage\ChatPackage\Repositories\ChatRoomRepository::class
        );

        $this->app->bind(
            \ChatPackage\ChatPackage\Repositories\Contracts\MessageRepositoryInterface::class,
            \ChatPackage\ChatPackage\Repositories\MessageRepository::class
        );

        // Bind service interface to implementation
        $this->app->bind(
            \ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface::class,
            \ChatPackage\ChatPackage\Services\ChatService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Auto-detect and use parent project's database connection
        $this->configureDatabase();

        // Publish migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Publish views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'chat-package');

        // Publish config
        $this->publishes([
            __DIR__.'/config/chat-package.php' => config_path('chat-package.php'),
        ], 'chat-package-config');

        // Load routes
        $this->loadRoutes();

        // Publish assets if needed
        $this->publishes([
            __DIR__.'/resources/assets' => public_path('vendor/chat-package'),
        ], 'chat-package-assets');
    }

    /**
     * Configure database connection to use parent project's database.
     */
    protected function configureDatabase(): void
    {
        // Use the default database connection from parent project
        // The package models will automatically use the parent's database connection
        // since they extend Illuminate\Database\Eloquent\Model
    }

    /**
     * Load package routes
     */
    protected function loadRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('chat')
            ->name('chat.')
            ->group(function () {
                require __DIR__.'/routes/web.php';
            });
    }
}

