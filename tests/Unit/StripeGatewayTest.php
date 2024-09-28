<?php

namespace Davidiwezulu\Ecommerce\Tests\Unit;

use Davidiwezulu\Ecommerce\Payments\StripeGateway;
use Mockery;
use Davidiwezulu\Ecommerce\Tests\TestCase;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Exception\AuthenticationException;

/**
 * Class StripeGatewayTest
 *
 * Contains unit tests for the StripeGateway class, ensuring that payment operations such as charging
 * payments function correctly. Utilizes Mockery to mock the StripeClient, enabling isolated and reliable
 * testing of the StripeGateway's functionalities without relying on external Stripe services.
 *
 * @package    Davidiwezulu\Ecommerce\Tests\Unit
 * @subpackage StripeGatewayTest
 * @category   Testing
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class StripeGatewayTest extends TestCase
{
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
     * Test charging a payment via Stripe.
     *
     * This test verifies that the `charge` method of the StripeGateway correctly initiates a payment
     * request to Stripe and returns the expected charge ID and status upon a successful transaction.
     * It mocks the StripeClient and its `charges` property to simulate a successful charge response
     * from Stripe.
     *
     * @return void
     *
     * @throws \Exception If the StripeGateway's charge method encounters an unexpected error.
     */
    public function testChargePayment(): void
    {
        // Mock the StripeClient to simulate interaction with Stripe's API
        $stripeClientMock = Mockery::mock(StripeClient::class);

        // Mock the charges property and its create method to simulate a successful charge
        $chargesMock = Mockery::mock();
        $chargesMock->expects('create')
            ->andReturns((object)[
                'id'     => 'ch_testCharge123',
                'status' => 'succeeded',
            ]);

        // Assign the mocked charges to the StripeClient mock
        $stripeClientMock->charges = $chargesMock;

        // Instantiate StripeGateway with the mocked StripeClient
        $stripeGateway = new StripeGateway($stripeClientMock);

        // Define the amount to be charged and the payment details
        $amount = 10.00;
        $paymentDetails = ['token' => 'tok_visa'];

        // Call the charge method to initiate the payment process
        $result = $stripeGateway->charge($amount, $paymentDetails);

        // Assertions to verify that the charge was processed correctly
        $this->assertEquals('ch_testCharge123', $result->id, 'The charge ID should match the mocked Stripe response.');
        $this->assertEquals('succeeded', $result->status, 'The charge status should be "succeeded".');
    }

    /**
     * Test charging a payment via Stripe throws an exception on authentication failure.
     *
     * This test ensures that the `charge` method of the StripeGateway correctly handles exceptions
     * thrown by the StripeClient, specifically authentication-related errors. It mocks the StripeClient
     * to throw an AuthenticationException and asserts that the StripeGateway propagates the exception.
     *
     * @return void
     *
     * @throws AuthenticationException Expected exception when authentication fails.
     */
    public function testChargePaymentThrowsException(): void
    {
        // Define the expected exception and message
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API Key');

        // Mock the StripeClient to simulate an authentication error
        $stripeClientMock = Mockery::mock(StripeClient::class);

        // Mock the charges property and its create method to throw an AuthenticationException
        $chargesMock = Mockery::mock();
        $chargesMock->expects('create')
            ->andThrow(new AuthenticationException('Invalid API Key', 401));

        // Assign the mocked charges to the StripeClient mock
        $stripeClientMock->charges = $chargesMock;

        // Instantiate StripeGateway with the mocked StripeClient
        $stripeGateway = new StripeGateway($stripeClientMock);

        // Define the amount to be charged and the payment details with an invalid token
        $amount = 10.00;
        $paymentDetails = ['token' => 'tok_invalid'];

        // Attempt to charge the payment, which should trigger the mocked exception
        $stripeGateway->charge($amount, $paymentDetails);
    }
}
