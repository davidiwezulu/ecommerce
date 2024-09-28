<?php

namespace Davidiwezulu\Ecommerce\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Davidiwezulu\Ecommerce\Repositories\ProductRepository;
use Davidiwezulu\Ecommerce\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CartService
 *
 * Manages shopping cart operations within the e-commerce system. This service provides functionalities
 * such as adding or updating items in the cart, removing items, retrieving cart contents, and calculating
 * totals with applicable taxes. By encapsulating these operations, the CartService promotes a clean
 * separation of concerns and enhances code maintainability and reusability.
 *
 * @package    Davidiwezulu\Ecommerce\Services
 * @subpackage CartService
 * @category   Service
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class CartService
{
    /**
     * Product repository instance.
     *
     * Utilizes the ProductRepository to perform data access operations related to products.
     *
     * @var ProductRepository
     */
    protected ProductRepository $productRepo;

    /**
     * CartItem model instance.
     *
     * Represents the CartItem model used for managing items in the user's shopping cart.
     *
     * @var Model
     */
    protected mixed $cartItemModel;

    /**
     * CartService constructor.
     *
     * Initializes the CartService by instantiating the ProductRepository and retrieving the CartItem model
     * from the configuration. This setup ensures that the service has the necessary dependencies to perform
     * its operations effectively.
     *
     * @return void
     */
    public function __construct()
    {
        $this->productRepo    = new ProductRepository();
        $this->cartItemModel = Config::get('ecommerce.models.cart_item', CartItem::class);
    }

    /**
     * Add or update a product in the cart, including tax calculations.
     *
     * Adds a specified quantity of a product to the user's cart. If the product already exists in the cart,
     * its quantity is incremented by the specified amount. The method also calculates the applicable tax
     * based on the product's tax rate and the system's tax configuration.
     *
     * @param int $productId The unique identifier of the product to add or update in the cart.
     * @param int $quantity  The quantity of the product to add to the cart.
     *
     * @return void
     *
     * @throws \Exception If the specified product does not exist.
     * @throws \RuntimeException If tax calculation fails or saving the cart item fails.
     */
    public function addOrUpdate(int $productId, int $quantity): void
    {
        // Ensure the product exists
        $product = $this->productRepo->find($productId);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        // Determine tax rate
        $taxRate = $product->tax_rate ?? config('ecommerce.tax.default_rate', 0.0);

        // Calculate tax amount
        $taxAmount = $this->calculateTax($product->price, $taxRate);

        // Find or create the cart item
        $cartItem = $this->cartItemModel::firstOrNew([
            'user_id'    => Auth::id(),
            'product_id' => $productId,
        ]);

        // Update price, tax amount, and quantity
        $cartItem->price      = $product->price;
        $cartItem->tax_amount = $taxAmount;
        $cartItem->quantity   += $quantity;
        $cartItem->save();
    }

    /**
     * Remove a product from the cart.
     *
     * Deletes a specified product from the user's shopping cart. If the product does not exist in the cart,
     * the method performs no action.
     *
     * @param int $productId The unique identifier of the product to remove from the cart.
     *
     * @return void
     *
     * @throws QueryException If the database query fails.
     */
    public function remove(int $productId): void
    {
        $this->cartItemModel::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * Adjusts the quantity of a specified product in the user's cart. If the product does not exist in the cart,
     * an exception is thrown.
     *
     * @param int $productId The unique identifier of the product to update.
     * @param int $quantity  The new quantity to set for the product in the cart.
     *
     * @return void
     *
     * @throws \Exception If the product is not found in the cart.
     * @throws QueryException If the database query fails.
     */
    public function update(int $productId, int $quantity): void
    {
        $cartItem = $this->cartItemModel::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            throw new \Exception('Product not found in cart');
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();
    }

    /**
     * Get all items in the cart for the current user.
     *
     * Retrieves a collection of all cart items associated with the authenticated user, including related product details.
     *
     * @return Collection A collection of CartItem model instances.
     *
     * @throws QueryException If the database query fails.
     */
    public function items(): Collection
    {
        return $this->cartItemModel::with('product')
            ->where('user_id', Auth::id())
            ->get();
    }

    /**
     * Clear all items in the cart for the current user.
     *
     * Removes all products from the authenticated user's shopping cart, effectively emptying the cart.
     *
     * @return void
     *
     * @throws QueryException If the database query fails.
     */
    public function clear(): void
    {
        $this->cartItemModel::where('user_id', Auth::id())->delete();
    }

    /**
     * Calculate tax amount based on price and tax rate.
     *
     * Computes the tax amount for a product based on its price and the applicable tax rate.
     * The calculation method depends on whether taxes are included in the product prices.
     *
     * @param float $price   The price of the product.
     * @param float $taxRate The tax rate applicable to the product.
     *
     * @return float The calculated tax amount.
     *
     * @throws \InvalidArgumentException If the provided price or tax rate is negative.
     */
    protected function calculateTax(float $price, float $taxRate): float
    {
        if ($price < 0 || $taxRate < 0) {
            throw new \InvalidArgumentException('Price and tax rate must be non-negative.');
        }

        if (config('ecommerce.tax.included_in_prices', false)) {
            // If tax is included in prices, extract it
            return $price - ($price / (1 + $taxRate));
        } else {
            // If tax is not included, calculate it
            return $price * $taxRate;
        }
    }
}
