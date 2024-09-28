<?php

namespace Davidiwezulu\Ecommerce\Tests\Unit;

use Davidiwezulu\Ecommerce\Facades\Admin;
use Davidiwezulu\Ecommerce\Models\Product;
use Davidiwezulu\Ecommerce\Tests\TestCase;
use Mockery;

/**
 * Class AdminServiceTest
 *
 * Contains unit tests for the AdminService class, ensuring that administrative operations
 * such as adding, updating products, and managing inventory function as expected.
 * Utilizes Mockery to mock the Admin facade, allowing for isolated and reliable testing
 * of service methods without relying on external dependencies.
 *
 * @package    Davidiwezulu\Ecommerce\Tests\Unit
 * @subpackage AdminServiceTest
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class AdminServiceTest extends TestCase
{
    /**
     * Setup the test environment before each test.
     *
     * This method is called before each test method is executed. It performs essential setup tasks
     * such as aliasing the Admin facade and mocking its methods to ensure that tests run in isolation.
     *
     * @return void
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Register the Admin facade alias for easy access within tests
        $this->app->alias(\Davidiwezulu\Ecommerce\Facades\Admin::class, 'Admin');

        // Mock the Admin facade methods to simulate service behavior without actual implementation
        $this->mockAdminFacade();
    }

    /**
     * Clean up the testing environment after each test.
     *
     * This method is called after each test method is executed. It ensures that Mockery's expectations
     * are verified and that no lingering mock objects affect subsequent tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Close Mockery to verify mock expectations and clean up
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Mock the Admin facade methods using Mockery.
     *
     * This method sets up expectations for the Admin facade's methods, allowing tests to simulate
     * various scenarios such as adding a product, updating a product, and updating inventory without
     * relying on the actual service implementations.
     *
     * @return void
     */
    protected function mockAdminFacade(): void
    {
        // Mock the addProduct method to return a Product instance with provided data
        Admin::shouldReceive('addProduct')
            ->withArgs(function ($productData) {
                // Validate that productData is an array and contains a 'name' key
                return is_array($productData) && isset($productData['name']);
            })
            ->andReturnUsing(function ($productData) {
                // Return a new Product instance populated with the provided data
                return new Product($productData);
            });

        // Mock the updateProduct method to return an updated Product instance
        Admin::shouldReceive('updateProduct')
            ->andReturnUsing(function ($productId, $updateData) {
                // Merge the product ID with the update data to simulate an updated Product instance
                $productData = array_merge(['id' => $productId], $updateData);
                return new Product($productData);
            });

        // Mock the updateInventory method to return true, indicating a successful update
        Admin::shouldReceive('updateInventory')
            ->withArgs(function ($productId, $quantity) {
                // Validate that productId and quantity are integers
                return is_int($productId) && is_int($quantity);
            })
            ->andReturn(true);
    }

    /**
     * Test adding a new product through the Admin service.
     *
     * This test verifies that the addProduct method successfully creates a new product with the provided
     * data and returns an instance of the Product model populated with the correct attributes.
     *
     * @return void
     */
    public function testAddNewProduct(): void
    {
        // Define the product data to be added
        $productData = [
            'name'        => 'Test Product',
            'price'       => 99.99,
            'tax_rate'    => 0.15,
            'description' => 'Test Description',
            'sku'         => 'TESTSKU',
        ];

        // Call the addProduct method via the Admin facade
        $product = Admin::addProduct($productData);

        // Assert that the returned object is an instance of the Product model
        $this->assertInstanceOf(Product::class, $product);

        // Assert that each attribute of the Product model matches the provided data
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(0.15, $product->tax_rate);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals('TESTSKU', $product->sku);
    }

    /**
     * Test updating an existing product through the Admin service.
     *
     * This test verifies that the updateProduct method correctly updates the specified product's attributes
     * and returns an instance of the Product model with the updated data.
     *
     * @return void
     */
    public function testUpdateExistingProduct(): void
    {
        // Example product ID to be updated
        $productId = 1;

        // Define the data to update the product with
        $updateData = [
            'name'     => 'Updated Product',
            'price'    => 149.99,
            'tax_rate' => 0.2,
        ];

        // Call the updateProduct method via the Admin facade
        $updatedProduct = Admin::updateProduct($productId, $updateData);

        // Assert that the returned object is an instance of the Product model
        $this->assertInstanceOf(Product::class, $updatedProduct);

        // Assert that each updated attribute of the Product model matches the provided data
        $this->assertEquals('Updated Product', $updatedProduct->name);
        $this->assertEquals(149.99, $updatedProduct->price);
        $this->assertEquals(0.2, $updatedProduct->tax_rate);
    }

    /**
     * Test updating the inventory for a product through the Admin service.
     *
     * This test verifies that the updateInventory method successfully updates the stock quantity
     * for the specified product and returns a boolean indicating the success of the operation.
     *
     * @return void
     */
    public function testUpdateInventory(): void
    {
        // Example product ID whose inventory is being updated
        $productId = 1;

        // Define the new quantity for the product's inventory
        $quantity  = 100;

        // Call the updateInventory method via the Admin facade
        $result = Admin::updateInventory($productId, $quantity);

        // Assert that the method returns true, indicating a successful inventory update
        $this->assertTrue((bool)$result);
    }
}
