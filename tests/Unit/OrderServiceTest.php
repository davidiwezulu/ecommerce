<?php


namespace Davidiwezulu\Ecommerce\Tests\Unit;

use Davidiwezulu\Ecommerce\Payments\StripeGateway;
use Davidiwezulu\Ecommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Davidiwezulu\Ecommerce\Models\Product;
use Davidiwezulu\Ecommerce\Models\User;
use Davidiwezulu\Ecommerce\Facades\Cart;
use Davidiwezulu\Ecommerce\Services\OrderService;
use Davidiwezulu\Ecommerce\Payments\PaymentGatewayInterface;
use Mockery;

/**
 * Class OrderServiceTest
 *
 * Contains unit tests for the OrderService class, ensuring that order creation and payment processing
 * function correctly across different payment gateways such as Stripe and PayPal. Utilizes Mockery to
 * mock payment gateway interactions, enabling isolated and reliable testing of the OrderService's
 * functionalities without relying on external services.
 *
 * @package    Davidiwezulu\Ecommerce\Tests\Unit
 * @subpackage OrderServiceTest
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating an order with the Stripe payment gateway.
     *
     * This test verifies that the OrderService correctly processes an order using the Stripe payment gateway.
     * It mocks the StripeGateway to simulate a successful charge and asserts that the order and order items
     * are correctly created in the database.
     *
     * @return void
     *
     * @throws \Exception If the StripeGateway's charge method fails or order creation encounters issues.
     */
    public function testCreateOrderWithStripe(): void
    {
        // Mock the StripeGateway to simulate payment processing
        $stripeGatewayMock = Mockery::mock(StripeGateway::class);
        $stripeGatewayMock->shouldReceive('charge')
            ->once()
            ->andReturn((object)[
                'id' => 'ch_testCharge123',
                'status' => 'succeeded',
            ]);

        // Inject the mocked StripeGateway into OrderService
        $orderService = new OrderService($stripeGatewayMock);

        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product with associated inventory
        $product = Product::factory()
            ->withInventory(10) // Set inventory quantity to 10
            ->create([
                'price'    => 100.00,
                'tax_rate' => 0.1,
            ]);

        // Add the product to the cart with a quantity of 2
        Cart::addOrUpdate($product->id, 2);

        // Prepare payment details for Stripe
        $paymentDetails = [
            'gateway' => 'stripe',
            'token' => 'test-stripe-token',
        ];

        // Retrieve cart items as an array
        $cartItems = Cart::items()->toArray();

        // Create the order using the OrderService
        $order = $orderService->create($user->id, $cartItems, $paymentDetails);

        // Assert that the order exists in the database with the correct user ID and status
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'processing',
        ]);

        // Assert that the order item exists in the database with the correct order ID and product ID
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test executing a PayPal payment and creating an order.
     *
     * This test verifies that the OrderService correctly executes a PayPal payment after user approval
     * and subsequently creates the corresponding order and order items in the database. It mocks the
     * PayPalGateway to simulate the execution of a PayPal order.
     *
     * @return void
     *
     * @throws \Exception If the PayPalGateway's execute method fails or order creation encounters issues.
     */
    public function testExecutePayPalPaymentAndCreateOrder()
    {
        // Mock the PayPalGateway
        $payPalGatewayMock = Mockery::mock(PayPalGateway::class, PaymentGatewayInterface::class);
        $payPalGatewayMock->shouldReceive('execute')
            ->once()
            ->with('ORDER-123456')
            ->andReturn((object)[
                'id' => 'ORDER-123456',
                'status' => 'COMPLETED',
            ]);

        // Inject the mocked PayPalGateway into OrderService
        $orderService = new OrderService($payPalGatewayMock);

        // Create a user and log them in
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product with associated inventory
        $product = Product::factory()
            ->withInventory(10) // Set inventory quantity to 10
            ->create([
                'price'    => 100.00,
                'tax_rate' => 0.1,
            ]);

        Cart::addOrUpdate($product->id, 1);

        // Retrieve cart items
        $cartItems = Cart::items()->toArray();

        // Execute payment and create order using the mocked $orderService
        $order = $orderService->executePayPalPayment('ORDER-123456', $user->id, $cartItems);

        // Assert order is created
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'status' => 'processing']);

        // Assert order items are created
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id]);
    }
}
