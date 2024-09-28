<?php

namespace Davidiwezulu\Ecommerce\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Davidiwezulu\Ecommerce\EcommerceServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Class TestCase
 *
 * Serves as the base test case for all feature and unit tests within the E-commerce package.
 * It extends Orchestra's Testbench to provide a Laravel application environment for testing.
 * This class handles the setup of the testing environment, including configuration, migrations,
 * and cache management, ensuring that each test runs in a clean and isolated state.
 *
 * @package    Davidiwezulu\Ecommerce\Tests
 * @subpackage TestCases
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     *
     * This method is called before each test is executed. It performs essential setup tasks such as
     * clearing caches, loading Laravel's default migrations, and loading package-specific migrations.
     * Ensures that each test starts with a fresh and consistent environment.
     *
     * @return void
     *
     * @throws \Exception If migration loading fails or other setup tasks encounter issues.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear caches programmatically to ensure a clean state
        $this->clearCaches();

        // Load Laravel's default migrations (users, password_resets, etc.)
        $this->loadLaravelMigrations();

        // Load package-specific migrations to set up the database schema required for testing
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Get package providers.
     *
     * Registers the EcommerceServiceProvider with the testing application. This ensures that all
     * services, configurations, and bindings defined in the service provider are available during tests.
     *
     * @param  Application  $app The application instance.
     * @return array<string>                         An array of service provider class names.
     */
    protected function getPackageProviders($app): array
    {
        return [
            EcommerceServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * Configures the environment for testing by setting up the database connection, currency settings,
     * tax configurations, and payment gateway credentials. Uses an in-memory SQLite database for
     * efficient and isolated testing without the need for an actual database server.
     *
     * @param  Application  $app The application instance.
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Configure the default database connection to use in-memory SQLite for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set up currency configurations
        $app['config']->set('ecommerce.currency.symbol', '$');
        $app['config']->set('ecommerce.currency.code', 'USD');

        // Configure tax settings
        $app['config']->set('ecommerce.tax.default_rate', 0.1); // 10% tax
        $app['config']->set('ecommerce.tax.included_in_prices', false);

        // Configure payment gateways with dummy data for testing purposes
        $app['config']->set('ecommerce.payment_gateways.stripe.secret_key', 'sk_test_dummy');
        $app['config']->set('ecommerce.payment_gateways.paypal.client_id', 'dummy_client_id');
        $app['config']->set('ecommerce.payment_gateways.paypal.secret', 'dummy_secret');
        $app['config']->set('ecommerce.payment_gateways.paypal.mode', 'sandbox');
    }

    /**
     * Clear Laravel's caches programmatically.
     *
     * Executes Artisan commands to clear various caches, including configuration, application,
     * route, and view caches. Ensures that cached data does not interfere with test outcomes,
     * promoting consistency and reliability in test results.
     *
     * @return void
     *
     * @throws CommandNotFoundException If an Artisan command is not found.
     */
    protected function clearCaches(): void
    {
        Artisan::call('config:clear'); // Clear configuration cache
        Artisan::call('cache:clear');  // Clear application cache
        Artisan::call('route:clear');  // Clear route cache
        Artisan::call('view:clear');   // Clear compiled view files
    }

    /**
     * Get package aliases.
     *
     * Registers facade aliases for the Admin, Cart, and Order services, allowing them to be
     * accessed via simple and expressive syntax within tests. Facilitates easier interaction
     * with the services without the need for dependency injection.
     *
     * @param  Application  $app The application instance.
     * @return array<string, string>                  An associative array mapping aliases to their corresponding facade classes.
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Admin'  => \Davidiwezulu\Ecommerce\Facades\Admin::class,
            'Cart'   => \Davidiwezulu\Ecommerce\Facades\Cart::class,
            'Order'  => \Davidiwezulu\Ecommerce\Facades\Order::class,
        ];
    }
}
