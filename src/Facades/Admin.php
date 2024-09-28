<?php

namespace Davidiwezulu\Ecommerce\Facades;

use Davidiwezulu\Ecommerce\Models\Product;
use Illuminate\Support\Facades\Facade;

/**
 * Class Admin
 *
 * The Admin Facade provides a static interface to the AdminService, enabling streamlined access
 * to administrative functionalities within the e-commerce system. Through this Facade, developers
 * can effortlessly add and update products, as well as manage inventory without directly interacting
 * with the underlying service layer.
 *
 * @package    Davidiwezulu\Ecommerce\Facades
 * @subpackage Admin
 * @category   Facade
 * @author     David Iwezulu
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @method static Product addProduct(array $data)
 *      Adds a new product to the e-commerce platform.
 *
 *      @param array $data  An associative array containing product details such as name, SKU, price, etc.
 *      @return Product  The newly created Product model instance.
 *
 * @method static Product updateProduct(int $productId, array $data)
 *      Updates an existing product identified by its ID with the provided data.
 *
 *      @param int   $productId  The unique identifier of the product to update.
 *      @param array $data       An associative array containing the updated product details.
 *      @return Product  The updated Product model instance.
 *
 * @method static void updateInventory(int $productId, int $quantity)
 *      Updates the inventory quantity for a specific product.
 *
 *      @param int $productId  The unique identifier of the product.
 *      @param int $quantity   The new inventory quantity to set.
 *
 * @see \Davidiwezulu\Ecommerce\Services\AdminService
 */
class Admin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * This method is used by the Facade to resolve the underlying service from the service container.
     * It returns the key used to bind the AdminService in the container.
     *
     * @return string  The service container binding key for AdminService.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'admin';
    }
}
