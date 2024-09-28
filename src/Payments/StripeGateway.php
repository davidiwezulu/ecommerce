<?php

namespace Davidiwezulu\Ecommerce\Payments;

use stdClass;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Config;
use Stripe\Exception\AuthenticationException;

/**
 * Class StripeGateway
 *
 * Handles payment processing through Stripe using the Stripe PHP SDK.
 * Implements the PaymentGatewayInterface to provide standardized methods for
 * charging and refunding payments. This class facilitates the creation of charges
 * and refunds, integrating seamlessly with the e-commerce system.
 *
 * @package    Davidiwezulu\Ecommerce\Payments
 * @subpackage Gateways
 * @category   Payment
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class StripeGateway implements PaymentGatewayInterface
{
    /**
     * StripeClient instance.
     *
     * Utilizes the Stripe PHP SDK to communicate with Stripe's API.
     *
     * @var \Stripe\StripeClient
     */
    protected StripeClient $stripeClient;

    /**
     * StripeGateway constructor.
     *
     * Initializes the StripeClient with the provided instance or creates a new one
     * using the secret key from the configuration. This allows for dependency injection
     * of a custom StripeClient instance, facilitating testing and flexibility.
     *
     * @param \Stripe\StripeClient|null $stripeClient Optional StripeClient instance.
     */
    public function __construct(StripeClient $stripeClient = null)
    {
        $this->stripeClient = $stripeClient ?: new StripeClient(config('ecommerce.payment_gateways.stripe.secret_key'));
    }

    /**
     * Charge the payment via Stripe.
     *
     * Initiates a charge for the specified amount using the provided payment details.
     * Converts the amount to the smallest currency unit (e.g., cents) and creates a charge
     * through Stripe's API. Handles authentication and general exceptions, ensuring robust
     * error handling and consistent responses.
     *
     * @param float $amount The total amount to be charged, in the major currency unit (e.g., dollars).
     * @param array $paymentDetails An associative array containing payment information such as:
     *                               - 'token' : string, the payment source token obtained from Stripe.js.
     *
     * @return Charge|stdClass|null The created Stripe charge object containing details of the transaction.
     *
     * @throws AuthenticationException If Stripe authentication fails due to invalid API credentials.
     */
    public function charge($amount, $paymentDetails): \Stripe\Charge|stdClass|null
    {
        try {
            return $this->stripeClient->charges->create([
                'amount'      => $this->convertAmountToCents($amount),
                'currency'    => Config::get('ecommerce.currency.code', 'GBP'),
                'source'      => $paymentDetails['token'],
                'description' => 'Order Payment',
            ]);
        } catch (AuthenticationException $e) {
            throw $e; // Rethrow the original Stripe exception
        } catch (\Exception $e) {
            throw new \RuntimeException('Stripe Payment Error: ' . $e->getMessage());
        }
    }

    /**
     * Refund the payment via Stripe.
     *
     * Initiates a refund for a previously processed charge using its unique transaction ID.
     * Creates a refund through Stripe's API and handles any exceptions that may occur during
     * the refund process, ensuring consistent error handling and responses.
     *
     * @param string $transactionId The unique identifier of the charge to be refunded.
     *
     * @return Refund|stdClass|null The created Stripe refund object containing details of the refund.
     *
     */
    public function refund($transactionId): \Stripe\Refund|stdClass|null
    {
        try {
            return $this->stripeClient->refunds->create([
                'charge' => $transactionId,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Stripe Refund Error: ' . $e->getMessage());
        }
    }

    /**
     * Convert the amount to the smallest currency unit.
     *
     * Stripe requires the amount to be specified in the smallest currency unit (e.g., cents).
     * This method ensures accurate conversion by multiplying the major unit amount by 100 and
     * casting it to an integer.
     *
     * @param float $amount The amount in the major currency unit (e.g., dollars).
     *
     * @return int          The amount converted to the smallest currency unit (e.g., cents).
     */
    protected function convertAmountToCents($amount): int
    {
        return (int)($amount * 100);
    }
}
