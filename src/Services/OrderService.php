<?php

namespace Davidiwezulu\Ecommerce\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Davidiwezulu\Ecommerce\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Davidiwezulu\Ecommerce\Models\Product;

/**
 * Class OrderService
 *
 * Manages order-related operations within the e-commerce system, including order creation,
 * payment processing, and integration with various payment gateways. This service encapsulates
 * the business logic required to handle customer orders, ensuring a seamless and secure
 * transaction process.
 *
 * @package    Davidiwezulu\Ecommerce\Services
 * @subpackage OrderService
 * @category   Service
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class OrderService
{
    /**
     * Payment gateway instance.
     *
     * Utilizes a payment gateway that implements the PaymentGatewayInterface to process payments.
     *
     * @var PaymentGatewayInterface
     */
    protected PaymentGatewayInterface $paymentGateway;

    /**
     * Order model instance.
     *
     * Represents the Order model used for managing order records in the database.
     *
     * @var Model
     */
    protected mixed $orderModel;

    /**
     * OrderItem model instance.
     *
     * Represents the OrderItem model used for managing individual items within an order.
     *
     * @var Model
     */
    protected mixed $orderItemModel;

    /**
     * OrderService constructor.
     *
     * Initializes the OrderService by setting up the payment gateway and retrieving the Order and
     * OrderItem models from the configuration. This setup ensures that the service is equipped
     * to handle order creation and payment processing effectively.
     *
     * @param PaymentGatewayInterface|null $paymentGateway Optional payment gateway instance for dependency injection.
     *
     * @return void
     */
    public function __construct(PaymentGatewayInterface $paymentGateway = null)
    {
        $this->paymentGateway  = $paymentGateway;
        $this->orderModel      = Config::get('ecommerce.models.order');
        $this->orderItemModel  = Config::get('ecommerce.models.order_item');
    }

    /**
     * Create a new order and process payment.
     *
     * Initiates the creation of a new order for the specified user, processes the payment through the
     * selected payment gateway, and handles redirection if necessary (e.g., for PayPal approvals).
     *
     * @param int   $userId         The unique identifier of the user placing the order.
     * @param array $cartItems      An array of cart items, each containing details such as product ID, quantity, price, and tax amount.
     * @param array $paymentDetails An associative array containing payment information, including:
     *                              - 'gateway'    : string, the key of the payment gateway to use (e.g., 'stripe', 'paypal').
     *                              - 'token'      : string, the payment source token (for gateways like Stripe).
     *                              - 'cancel_url' : string, the URL to redirect the user if they cancel the payment (for gateways like PayPal).
     *                              - 'return_url' : string, the URL to redirect the user after approving the payment (for gateways like PayPal).
     *
     * @return Model|RedirectResponse Returns the created Order model instance for synchronous payment gateways,
     *                                                                        or a RedirectResponse for gateways requiring user redirection (e.g., PayPal).
     *
     * @throws \Exception If the specified payment gateway is not supported or if payment processing fails.
     */
    public function create($userId, $cartItems, $paymentDetails)
    {
        // Validate inventory levels
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                throw new \Exception("Product not found with ID {$item['product_id']}");
            }

            if (!$product->inventory || $product->inventory->quantity < $item['quantity']) {
                throw new \Exception("Insufficient inventory for product ID {$item['product_id']}");
            }
        }

        // Proceed with payment processing
        $total = $this->calculateTotal($cartItems);

        if (!$this->paymentGateway) {
            $gatewayKey = $paymentDetails['gateway'] ?? 'stripe';
            $gatewayConfig = Config::get("ecommerce.payment_gateways.{$gatewayKey}");
            $gatewayClass = $gatewayConfig['class'];
            $this->paymentGateway = new $gatewayClass();
        }

        $paymentResponse = $this->paymentGateway->charge($total, $paymentDetails);

        // Handle PayPal redirection
        if ($paymentDetails['gateway'] === 'paypal' && isset($paymentResponse['redirect_url'])) {
            session(['paypal_order_id' => $paymentResponse['order_id']]);
            return redirect()->away($paymentResponse['redirect_url']);
        }

        // Create order and update inventory
        return $this->createOrder($userId, $cartItems, $total);
    }


    /**
     * Execute PayPal payment after user approval.
     *
     * Completes the payment process for PayPal by capturing the approved order and creating the corresponding
     * order and order items in the database.
     *
     * @param string $orderId   The unique identifier of the PayPal order to execute.
     * @param int    $userId    The unique identifier of the user placing the order.
     * @param array  $cartItems An array of cart items, each containing details such as product ID, quantity, price, and tax amount.
     *
     * @return Model The created Order model instance reflecting the successful payment.
     *
     * @throws \Exception If the payment gateway is not set or if payment execution fails.
     */
    public function executePayPalPayment($orderId, $userId, $cartItems)
    {
        if (!$this->paymentGateway) {
            throw new \Exception('Payment gateway not set.');
        }

        $this->paymentGateway->execute($orderId);

        $total = $this->calculateTotal($cartItems);
        return $this->createOrder($userId, $cartItems, $total);
    }

    /**
     * Calculate the total amount for the cart items, including taxes.
     *
     * Computes the cumulative total by summing up the line totals of each cart item, which include both
     * the product price and the applicable tax amount multiplied by the quantity.
     *
     * @param array $cartItems An array of cart items, each containing:
     *                         - 'price'      : float, the price of the product.
     *                         - 'tax_amount' : float, the tax amount for the product.
     *                         - 'quantity'   : int, the quantity of the product.
     *
     * @return float The total amount for the order, inclusive of taxes.
     */
    protected function calculateTotal($cartItems)
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $lineTotal = ($item['price'] + $item['tax_amount']) * $item['quantity'];
            $total += $lineTotal;
        }
        return $total;
    }

    /**
     * Create an order and associated order items.
     *
     * Inserts a new order record into the database and creates corresponding order item records for each
     * product included in the cart. This method ensures that the order and its items are correctly linked
     * and reflect the accurate total amount.
     *
     * @param int   $userId    The unique identifier of the user placing the order.
     * @param array $cartItems An array of cart items, each containing details such as product ID, quantity, price, and tax amount.
     * @param float $total     The total amount for the order, inclusive of taxes.
     *
     * @return Model The created Order model instance.
     *
     * @throws ModelNotFoundException If the associated inventory record does not exist.
     * @throws QueryException               If the database query fails.
     */
    protected function createOrder($userId, $cartItems, $total)
    {
        return DB::transaction(function () use ($userId, $cartItems, $total) {
            $order = $this->orderModel::create([
                'user_id' => $userId,
                'total'   => $total,
                'status'  => 'processing',
            ]);

            foreach ($cartItems as $item) {
                $this->orderItemModel::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'tax_rate'   => $item['product']['tax_rate'] ?? config('ecommerce.tax.default_rate', 0.0),
                    'tax_amount' => $item['tax_amount'],
                ]);

                // Update inventory
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found with ID {$item['product_id']}");
                }

                $product->sell($item['quantity']);
            }

            return $order;
        });
    }
}
