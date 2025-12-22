<?php

namespace ChatPackage\ChatPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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

        // Register broadcast authentication routes
        $this->registerBroadcastRoutes();

        // Load broadcast channels
        $this->loadBroadcastChannels();

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
     * 
     * This package is 100% API-based. All functionality is available via
     * /api/chat/* endpoints. Web routes are completely removed.
     */
    protected function loadRoutes(): void
    {
        // Load API routes only - this package is fully API-based
        // All routes return JSON responses and work with any frontend framework
        // Apply web middleware first (for session/CSRF), then auth middleware
        Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'api/chat', 'as' => 'api.chat.'], function () {
            require __DIR__.'/routes/api.php';
        });
    }

    /**
     * Register broadcast authentication routes
     * 
     * These routes are globally accessible and handle authentication
     * for real-time broadcasting channels (Pusher, etc.)
     */
    protected function registerBroadcastRoutes(): void
    {
        // Register broadcast routes with web middleware for session/CSRF support
        // The routes are available at /broadcasting/auth globally
        Broadcast::routes(['middleware' => ['web', 'auth']]);
    }

    /**
     * Load broadcast channel authorization callbacks
     */
    protected function loadBroadcastChannels(): void
    {
        // Load channel authorization callbacks from package
        require __DIR__.'/routes/channels.php';
    }
}

