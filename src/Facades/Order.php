<?php

namespace Davidiwezulu\Ecommerce\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Order
 *
 * The Order Facade provides a static interface to the OrderService, enabling streamlined access
 * to order-related functionalities within the e-commerce system. Through this Facade, developers
 * can effortlessly create new orders, retrieve existing orders, and update order statuses without
 * directly interacting with the underlying service layer.
 *
 * @package    Davidiwezulu\Ecommerce\Facades
 * @subpackage Order
 * @category   Facade
 * @author     David Iwezulu
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @method static \Davidiwezulu\Ecommerce\Models\Order create(int $userId, array $cartItems, array $paymentDetails)
 *      Creates a new order for a specified user with provided cart items and payment details.
 *
 *      @param int   $userId          The unique identifier of the user placing the order.
 *      @param array $cartItems       An array of cart items, each containing product ID and quantity.
 *      @param array $paymentDetails  An associative array containing payment information such as payment method, transaction ID, etc.
 *      @return \Davidiwezulu\Ecommerce\Models\Order  The newly created Order model instance.
 *
 * @method static \Davidiwezulu\Ecommerce\Models\Order find(int $orderId)
 *      Retrieves an existing order by its unique identifier.
 *
 *      @param int $orderId  The unique identifier of the order to retrieve.
 *      @return \Davidiwezulu\Ecommerce\Models\Order|null  The Order model instance if found, or null if not found.
 *
 * @method static void updateStatus(int $orderId, string $status)
 *      Updates the status of a specific order.
 *
 *      @param int    $orderId  The unique identifier of the order to update.
 *      @param string $status   The new status to set for the order (e.g., 'processing', 'completed', 'cancelled').
 *
 * @see \Davidiwezulu\Ecommerce\Services\OrderService
 */
class Order extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * This method is used by the Facade to resolve the underlying service from the service container.
     * It returns the key used to bind the OrderService in the container.
     *
     * @return string  The service container binding key for OrderService.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'order';
    }
}
