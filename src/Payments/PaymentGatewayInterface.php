<?php

namespace Davidiwezulu\Ecommerce\Payments;

/**
 * Interface PaymentGatewayInterface
 *
 * Defines the contract for payment gateway implementations within the e-commerce system.
 * This interface ensures that all payment gateways provide consistent methods for processing
 * charges and refunds, facilitating interoperability and scalability across different payment providers.
 *
 * @package    Davidiwezulu\Ecommerce\Payments
 * @subpackage Interfaces
 * @category   Payment
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
interface PaymentGatewayInterface
{
    /**
     * Charge the payment.
     *
     * Processes a payment charge of the specified amount using the provided payment details.
     * This method interacts with the payment gateway to authorize and capture funds.
     *
     * @param float $amount         The amount to be charged, in the smallest currency unit (e.g., cents).
     * @param array $paymentDetails An associative array containing payment information.
     *
     * @return mixed                The response from the payment gateway, typically containing transaction details.
     *
     * @throws PaymentException      If the payment charge fails due to reasons such as:
     *                               - Insufficient funds.
     *                               - Invalid payment details.
     *                               - Gateway connectivity issues.
     */
    public function charge($amount, $paymentDetails);

    /**
     * Refund the payment.
     *
     * Initiates a refund for a previously processed transaction using its unique transaction ID.
     * This method interacts with the payment gateway to reverse the charge and return funds to the customer.
     *
     * @param string $transactionId The unique identifier of the transaction to be refunded.
     *
     * @return mixed                The response from the refund operation, typically containing refund details.
     *
     * @throws PaymentException      If the refund process fails due to reasons such as:
     *                               - Invalid or non-existent transaction ID.
     *                               - Refund amount exceeding the original charge.
     *                               - Gateway connectivity issues.
     */
    public function refund($transactionId);
}
