<?php

namespace Davidiwezulu\Ecommerce\Payments;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

/**
 * Class PayPalGateway
 *
 * Handles payment processing through PayPal using the PayPal Checkout SDK.
 * Implements the PaymentGatewayInterface to provide standardized methods for
 * charging and refunding payments. This class facilitates the creation and
 * execution of PayPal orders, integrating seamlessly with the e-commerce system.
 *
 * @package    Davidiwezulu\Ecommerce\Payments
 * @subpackage Gateways
 * @category   Payment
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class PayPalGateway implements PaymentGatewayInterface
{
    /**
     * PayPal HTTP client instance.
     *
     * Utilizes the PayPalCheckoutSdk to communicate with PayPal's API.
     *
     * @var PayPalHttpClient
     */
    protected PayPalHttpClient $client;

    /**
     * PayPalGateway constructor.
     *
     * Initializes the PayPal HTTP client based on the configured environment
     * (sandbox or production) and credentials. Allows for dependency injection
     * of a custom PayPalHttpClient instance, facilitating testing and flexibility.
     *
     * @param PayPalHttpClient|null $client Optional PayPal HTTP client instance.
     */
    public function __construct(PayPalHttpClient $client = null)
    {
        $clientId = config('ecommerce.payment_gateways.paypal.client_id');
        $clientSecret = config('ecommerce.payment_gateways.paypal.secret');
        $mode = config('ecommerce.payment_gateways.paypal.mode', 'sandbox');

        $environment = $mode === 'sandbox'
            ? new SandboxEnvironment($clientId, $clientSecret)
            : new ProductionEnvironment($clientId, $clientSecret);

        $this->client = $client ?: new PayPalHttpClient($environment);
    }

    /**
     * Charge the payment via PayPal.
     *
     * Initiates a PayPal order with the specified amount and payment details.
     * Constructs the order creation request, executes it, and retrieves the
     * approval URL for user redirection.
     *
     * @param float $amount         The total amount to be charged, in the smallest currency unit (e.g., cents).
     * @param array $paymentDetails An associative array containing payment information such as:
     *                               - 'cancel_url' : string, the URL to redirect the user if they cancel the payment.
     *                               - 'return_url' : string, the URL to redirect the user after approving the payment.
     *
     * @return array                An array containing the PayPal order ID and approval URL.
     *
     * @throws \Exception           If the payment initiation fails due to reasons such as:
     *                               - Invalid payment details.
     *                               - PayPal API errors.
     *                               - Network connectivity issues.
     */
    public function charge($amount, $paymentDetails): array
    {
        $currencyCode = config('ecommerce.currency.code', 'GBP');

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => $currencyCode,
                    "value" => number_format($amount, 2, '.', '')
                ]
            ]],
            "application_context" => [
                "cancel_url" => $paymentDetails['cancel_url'],
                "return_url" => $paymentDetails['return_url'],
                "brand_name" => config('app.name'),
                "user_action" => "PAY_NOW"
            ]
        ];

        try {
            $response = $this->client->execute($request);

            $orderId = $response->result->id;
            $approveLink = null;
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    $approveLink = $link->href;
                    break;
                }
            }

            return [
                'order_id' => $orderId,
                'redirect_url' => $approveLink,
            ];
        } catch (\Exception $e) {
            throw new \Exception('PayPal Payment Error: ' . $e->getMessage());
        }
    }

    /**
     * Execute the PayPal payment after user approval.
     *
     * Captures the funds for a PayPal order that the user has approved.
     * Constructs the order capture request and executes it to finalize the payment.
     *
     * @param string $orderId The PayPal order ID obtained during the charge process.
     *
     * @return object          The captured order result containing details such as status, purchase units, and more.
     *
     * @throws \Exception      If payment execution fails due to reasons such as:
     *                          - Invalid or expired order ID.
     *                          - PayPal API errors.
     *                          - Network connectivity issues.
     */
    public function execute($orderId): object
    {
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');

        try {
            $response = $this->client->execute($request);
            return $response->result;
        } catch (\Exception $e) {
            throw new \Exception('PayPal Execution Error: ' . $e->getMessage());
        }
    }

    /**
     * Refund the payment.
     *
     * Initiates a refund for a previously processed transaction using its unique transaction ID.
     * **Note:** This method is not implemented for PayPal in this class and will throw an exception if called.
     *
     * @param string $transactionId The unique identifier of the transaction to be refunded.
     *
     * @return void
     *
     * @throws \Exception           Always throws an exception indicating that refunding is not implemented.
     */
    public function refund($transactionId): void
    {
        throw new \Exception('PayPal refund not implemented.');
    }
}
