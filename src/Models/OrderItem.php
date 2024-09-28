<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class OrderItem
 *
 * Represents an individual item within a customer's order in the e-commerce system.
 * This model encapsulates the details of a product ordered, including its quantity, price,
 * applicable tax rates, and the calculated tax amount. It establishes relationships with
 * the associated Order and Product models, facilitating seamless data retrieval and manipulation
 * related to order items.
 *
 * @package    Davidiwezulu\Ecommerce\Models
 * @subpackage OrderItem
 * @category   Model
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @property int          $id           The unique identifier for the order item.
 * @property int          $order_id     The unique identifier of the associated order.
 * @property int          $product_id   The unique identifier of the associated product.
 * @property int          $quantity     The quantity of the product ordered.
 * @property float        $price        The price of a single unit of the product at the time of order.
 * @property float|null   $tax_rate     The applicable tax rate for the product.
 * @property float        $tax_amount   The calculated tax amount for the product.
 * @property Carbon|null  $created_at   The timestamp when the order item was created.
 * @property Carbon|null  $updated_at   The timestamp when the order item was last updated.
 *
 * @property-read Order     $order        The associated Order model instance.
 * @property-read Product   $product      The associated Product model instance.
 * @property-read float     $total_price  The total price for the order item, calculated as (price + tax_amount) * quantity.
 */
class OrderItem extends Model
{
    /**
     * The table associated with the model.
     *
     * Retrieves the table name from the configuration to ensure flexibility across different environments.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *
     * Specifies which attributes can be bulk-assigned, enhancing security by preventing mass assignment vulnerabilities.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'tax_rate',
        'tax_amount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * Ensures that certain attributes are automatically cast to appropriate data types when accessed or mutated.
     *
     * @var array
     */
    protected $casts = [
        'order_id'   => 'integer',
        'product_id' => 'integer',
        'quantity'   => 'integer',
        'price'      => 'float',
        'tax_rate'   => 'float',
        'tax_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * OrderItem constructor.
     *
     * Initializes the model by setting the table name based on the application's configuration.
     * Ensures that the model interacts with the correct database table.
     *
     * @param array $attributes The model's attributes.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Retrieve the table name from the configuration, defaulting to 'order_items' if not set.
        $this->table = config('ecommerce.table_names.order_items', 'order_items');
    }

    /**
     * Get the order that owns the order item.
     *
     * Defines an inverse one-to-many relationship between OrderItem and Order.
     * Allows access to the order details directly from the order item.
     *
     * @return BelongsTo The relationship instance.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(
            config('ecommerce.models.order', Order::class),
            'order_id'
        );
    }

    /**
     * Get the product associated with the order item.
     *
     * Defines an inverse one-to-many relationship between OrderItem and Product.
     * Allows access to the product details directly from the order item.
     *
     * @return BelongsTo The relationship instance.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            config('ecommerce.models.product', Product::class),
            'product_id'
        );
    }

    /**
     * Accessor for the total price attribute.
     *
     * Calculates the total price for the order item by adding the tax amount to the price
     * and then multiplying by the quantity. This provides a convenient way to retrieve
     * the total cost associated with this order item.
     *
     * @return float The calculated total price.
     */
    public function getTotalPriceAttribute(): float
    {
        return ($this->price + $this->tax_amount) * $this->quantity;
    }

    /**
     * Scope a query to only include order items with a minimum quantity.
     *
     * Useful for retrieving order items that meet or exceed a specified quantity threshold.
     *
     * @param Builder $query    The Eloquent query builder.
     * @param int                                  $minQty   The minimum quantity threshold.
     * @return Builder           The modified query builder.
     */
    public function scopeWithMinimumQuantity($query, int $minQty): Builder
    {
        return $query->where('quantity', '>=', $minQty);
    }

    /**
     * Update the quantity of the order item.
     *
     * Provides a method to adjust the quantity while ensuring that the quantity remains positive.
     *
     * @param int $newQuantity The new quantity to set for the order item.
     * @return bool            Indicates whether the update was successful.
     *
     * @throws \InvalidArgumentException If the provided quantity is not a positive integer.
     */
    public function updateQuantity(int $newQuantity): bool
    {
        if ($newQuantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be a positive integer.");
        }

        $this->quantity = $newQuantity;
        return $this->save();
    }

    /**
     * Calculate and update the tax amount based on the current tax rate and price.
     *
     * Ensures that the tax amount reflects the latest tax rate and product price.
     *
     * @return bool Indicates whether the tax amount update was successful.
     */
    public function recalculateTaxAmount(): bool
    {
        if ($this->tax_rate !== null) {
            $this->tax_amount = ($this->price * $this->tax_rate) / 100;
            return $this->save();
        }

        // If tax_rate is null, ensure tax_amount is also null.
        $this->tax_amount = null;
        return $this->save();
    }
}
