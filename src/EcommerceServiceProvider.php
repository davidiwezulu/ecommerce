<?php

namespace Davidiwezulu\Ecommerce;

use Davidiwezulu\Ecommerce\Services;
use Illuminate\Support\ServiceProvider;

/**
 * Class EcommerceServiceProvider
 *
 * Registers and bootstraps the e-commerce services, configurations, and migrations within the Laravel application.
 * This service provider is responsible for binding essential services like CartService, OrderService, and AdminService
 * into the service container, as well as publishing configuration files and migrations to the application's directories.
 *
 * @package    Davidiwezulu\Ecommerce
 * @subpackage ServiceProviders
 * @category   ServiceProvider
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class EcommerceServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * This method is called by Laravel to bind services into the service container.
     * It merges the package configuration with the application's existing configuration,
     * binds singleton instances of CartService, OrderService, and AdminService,
     * and ensures that these services are readily available throughout the application.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge package configuration with the application's configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/ecommerce.php', 'ecommerce');

        // Bind CartService as a singleton in the service container
        $this->app->singleton('cart', function ($app) {
            return new Services\CartService();
        });

        // Bind OrderService as a singleton in the service container
        $this->app->singleton('order', function ($app) {
            return new Services\OrderService();
        });

        // Bind AdminService as a singleton in the service container
        $this->app->singleton('admin', function ($app) {
            return new Services\AdminService();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * This method is called after all other service providers have been registered.
     * It handles the publishing of configuration files and migrations to the application's
     * directories, making them accessible for customization and execution.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish the configuration file to the application's config directory
        $this->publishes([
            __DIR__ . '/../config/ecommerce.php' => config_path('ecommerce.php'),
        ], 'config');

        // Check if the application is running in the console before publishing migrations
        if ($this->app->runningInConsole()) {
            // Publish the migration files to the application's migrations directory
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'migrations');
        }

        // Load migration files from the package's migrations directory
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
