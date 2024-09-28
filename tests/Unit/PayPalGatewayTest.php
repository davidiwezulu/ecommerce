<?php

namespace Davidiwezulu\Ecommerce\Tests\Unit;

use Davidiwezulu\Ecommerce\Tests\TestCase;
use Davidiwezulu\Ecommerce\Payments\PayPalGateway;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Mockery;
use Mockery\MockInterface;

/**
 * Class PayPalGatewayTest
 *
 * Contains unit tests for the PayPalGateway class, ensuring that payment operations such as charging
 * and executing payments interact correctly with the PayPal API. Utilizes Mockery to simulate PayPal
 * client responses, enabling isolated and reliable testing of the PayPalGateway's functionalities
 * without relying on external PayPal services.
 *
 * @package    Davidiwezulu\Ecommerce\Tests\Unit
 * @subpackage PayPalGatewayTest
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class PayPalGatewayTest extends TestCase
{
    /**
     * Test charging a payment via PayPal.
     *
     * This test verifies that the `charge` method of the PayPalGateway correctly initiates a payment
     * request to PayPal and returns the expected order ID and redirect URL for user approval.
     * It mocks the PayPalHttpClient to simulate a successful payment creation response from PayPal.
     *
     * @return void
     *
     * @throws \Exception If the PayPalGateway's charge method encounters an unexpected error.
     */
    public function testCharge(): void
    {
        // Mock the PayPalHttpClient to simulate interaction with PayPal's API
        $clientMock = Mockery::mock(PayPalHttpClient::class);

        // Mock the response object returned by PayPal after creating an order
        $responseMock = (object)[
            'result' => (object)[
                'id' => 'ORDER-123456',
                'links' => [
                    (object)['rel' => 'approve', 'href' => 'https://www.paypal.com/checkoutnow?token=ORDER-123456']
                ]
            ]
        ];

        // Define the expectation for the execute method on the PayPalHttpClient
        $clientMock->shouldReceive('execute')
            ->once()
            ->andReturn($responseMock);

        // Instantiate the PayPalGateway with the mocked PayPalHttpClient
        $gateway = new PayPalGateway($clientMock);

        // Define the amount to be charged and the necessary payment details
        $amount = 100.00;
        $paymentDetails = [
            'return_url' => 'https://yourapp.com/return',
            'cancel_url' => 'https://yourapp.com/cancel',
        ];

        // Invoke the charge method to initiate the payment process
        $result = $gateway->charge($amount, $paymentDetails);

        // Assert that the returned order ID matches the mocked response
        $this->assertEquals('ORDER-123456', $result['order_id'], 'The order ID should match the PayPal response.');

        // Assert that the redirect URL matches the mocked response
        $this->assertEquals(
            'https://www.paypal.com/checkoutnow?token=ORDER-123456',
            $result['redirect_url'],
            'The redirect URL should match the PayPal approval link.'
        );
    }

    /**
     * Test executing a PayPal payment.
     *
     * This test verifies that the `execute` method of the PayPalGateway successfully completes a payment
     * after user approval. It mocks the PayPalHttpClient to simulate a successful payment execution response
     * from PayPal and asserts that the returned result contains the expected order ID and status.
     *
     * @return void
     *
     * @throws \Exception If the PayPalGateway's execute method encounters an unexpected error.
     */
    public function testExecutePayment(): void
    {
        // Mock the PayPalHttpClient to simulate interaction with PayPal's API
        $clientMock = Mockery::mock(PayPalHttpClient::class);

        // Mock the response object returned by PayPal after executing an order
        $responseMock = (object)[
            'result' => (object)[
                'id'     => 'ORDER-123456',
                'status' => 'COMPLETED',
            ]
        ];

        // Define the expectation for the execute method on the PayPalHttpClient
        $clientMock->expects('execute')
            ->andReturns($responseMock);

        // Instantiate the PayPalGateway with the mocked PayPalHttpClient
        $gateway = new PayPalGateway($clientMock);

        // Execute the payment for the given order ID
        $result = $gateway->execute('ORDER-123456');

        // Assert that the returned order ID matches the mocked response
        $this->assertEquals('ORDER-123456', $result->id, 'The executed order ID should match the PayPal response.');

        // Assert that the payment status is 'COMPLETED' as per the mocked response
        $this->assertEquals('COMPLETED', $result->status, 'The payment status should be COMPLETED.');
    }

    /**
     * Test executing a PayPal payment throws an exception.
     *
     * This test ensures that the `execute` method of the PayPalGateway correctly handles exceptions
     * thrown by the PayPalHttpClient. It mocks the PayPalHttpClient to throw an exception during
     * payment execution and asserts that the PayPalGateway propagates the exception with an appropriate message.
     *
     * @return void
     *
     * @throws \Exception Expected exception when payment execution fails.
     */
    public function testExecutePaymentThrowsException(): void
    {
        // Define the expected exception and message
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PayPal Execution Error: Some error message');

        // Mock the PayPalHttpClient to simulate an error during payment execution
        $clientMock = Mockery::mock(PayPalHttpClient::class);

        // Define the expectation for the execute method to throw an exception
        $clientMock->expects('execute')
            ->andThrow(new \Exception('Some error message'));

        // Instantiate the PayPalGateway with the mocked PayPalHttpClient
        $gateway = new PayPalGateway($clientMock);

        // Attempt to execute the payment, which should trigger the mocked exception
        $gateway->execute('ORDER-123456');
    }
}
