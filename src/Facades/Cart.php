<?php

namespace Davidiwezulu\Ecommerce\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Class Cart
 *
 * The Cart Facade provides a static interface to the CartService, enabling streamlined access
 * to shopping cart functionalities within the e-commerce system. Through this Facade, developers
 * can effortlessly add, remove, and update products in the cart, as well as retrieve the current
 * items in the cart without directly interacting with the underlying service layer.
 *
 * @package    Davidiwezulu\Ecommerce\Facades
 * @subpackage Cart
 * @category   Facade
 * @author     David Iwezulu
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @method static void add(int $productId, int $quantity)
 *      Adds a specified quantity of a product to the cart.
 *
 *      @param int $productId  The unique identifier of the product to add.
 *      @param int $quantity   The quantity of the product to add to the cart.
 *
 * @method static void remove(int $productId)
 *      Removes a product entirely from the cart.
 *
 *      @param int $productId  The unique identifier of the product to remove.
 *
 * @method static void update(int $productId, int $quantity)
 *      Updates the quantity of a specific product in the cart.
 *
 *      @param int $productId  The unique identifier of the product to update.
 *      @param int $quantity   The new quantity to set for the product in the cart.
 *
 * @method static Collection items()
 *      Retrieves all items currently in the cart.
 *
 *      @return Collection  A collection of cart items.
 *
 * @see \Davidiwezulu\Ecommerce\Services\CartService
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * This method is used by the Facade to resolve the underlying service from the service container.
     * It returns the key used to bind the CartService in the container.
     *
     * @return string  The service container binding key for CartService.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
