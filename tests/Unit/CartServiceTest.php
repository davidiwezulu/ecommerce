<?php

namespace Davidiwezulu\Ecommerce\Tests\Unit;

use Davidiwezulu\Ecommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Davidiwezulu\Ecommerce\Facades\Cart;
use Davidiwezulu\Ecommerce\Models\Product;
use Davidiwezulu\Ecommerce\Models\User;

/**
 * Class CartServiceTest
 *
 * Contains unit tests for the CartService class, ensuring that cart operations such as adding,
 * updating, removing items, and clearing the cart function as expected. Utilizes Laravel's
 * testing framework along with the RefreshDatabase trait to maintain a clean testing environment.
 *
 * @package    Davidiwezulu\Ecommerce\Tests\Unit
 * @subpackage CartServiceTest
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test adding a product to the cart.
     *
     * This test verifies that when a user adds a product to the cart, the cart contains the correct
     * number of items with accurate details such as product ID, quantity, price, and tax amount.
     *
     * @return void
     */
    public function testAddProductToCart(): void
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product with a specified price and tax rate
        $product = Product::factory()->create([
            'price'    => 100.00,
            'tax_rate' => 0.2, // 20% tax rate
        ]);

        // Add the product to the cart with a quantity of 2
        Cart::addOrUpdate($product->id, 2);

        // Retrieve all items in the cart
        $cartItems = Cart::items();

        // Assert that the cart contains exactly one item
        $this->assertCount(1, $cartItems);

        // Retrieve the first (and only) cart item
        $cartItem = $cartItems->first();

        // Assert that the cart item's product ID matches the added product
        $this->assertEquals($product->id, $cartItem->product_id);

        // Assert that the quantity of the cart item is 2
        $this->assertEquals(2, $cartItem->quantity);

        // Assert that the price of the cart item matches the product's price
        $this->assertEquals(100.00, $cartItem->price);

        // Assert that the tax amount is correctly calculated (100 * 0.2 = 20)
        $this->assertEquals(20.00, $cartItem->tax_amount);
    }

    /**
     * Test updating a product's quantity in the cart.
     *
     * This test ensures that updating the quantity of an existing product in the cart correctly
     * reflects the new quantity without altering other cart item details.
     *
     * @return void
     */
    public function testUpdateProductQuantityInCart(): void
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product with a specified price and tax rate
        $product = Product::factory()->create([
            'price'    => 50.00,
            'tax_rate' => 0.1, // 10% tax rate
        ]);

        // Add the product to the cart with a quantity of 1
        Cart::addOrUpdate($product->id, 1);

        // Update the quantity of the product in the cart to 5
        Cart::update($product->id, 5);

        // Retrieve the first cart item
        $cartItem = Cart::items()->first();

        // Assert that the quantity of the cart item has been updated to 5
        $this->assertEquals(5, $cartItem->quantity);
    }

    /**
     * Test removing a product from the cart.
     *
     * This test verifies that removing a product from the cart successfully deletes the cart item,
     * resulting in an empty cart.
     *
     * @return void
     */
    public function testRemoveProductFromCart(): void
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product
        $product = Product::factory()->create();

        // Add the product to the cart with a quantity of 1
        Cart::addOrUpdate($product->id, 1);

        // Remove the product from the cart
        Cart::remove($product->id);

        // Retrieve all items in the cart
        $cartItems = Cart::items();

        // Assert that the cart is now empty
        $this->assertCount(0, $cartItems);
    }

    /**
     * Test clearing the cart.
     *
     * This test ensures that clearing the cart removes all products, resulting in an empty cart.
     *
     * @return void
     */
    public function testClearCart(): void
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create multiple products
        $products = Product::factory()->count(3)->create();

        // Add each product to the cart with a quantity of 2
        foreach ($products as $product) {
            Cart::addOrUpdate($product->id, 2);
        }

        // Clear all items from the cart
        Cart::clear();

        // Retrieve all items in the cart
        $cartItems = Cart::items();

        // Assert that the cart is now empty
        $this->assertCount(0, $cartItems);
    }
}
