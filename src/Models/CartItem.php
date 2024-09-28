<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class CartItem
 *
 * Represents an individual item within a user's shopping cart in the e-commerce system.
 * This model encapsulates the details of the product added to the cart, including pricing,
 * tax calculations, and quantity management. It establishes relationships with the associated
 * Product and User models, facilitating seamless data retrieval and manipulation.
 *
 * @package    Davidiwezulu\Ecommerce\Models
 * @subpackage CartItem
 * @category   Model
 * @author     David Iwezulu
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @property int                      $id            The unique identifier for the cart item.
 * @property int|null                 $user_id       The unique identifier of the user who owns the cart item.
 * @property int                      $product_id    The unique identifier of the associated product.
 * @property float                    $price         The price of a single unit of the product at the time of addition to the cart.
 * @property float                    $tax_amount    The calculated tax amount applicable to the product.
 * @property int                      $quantity      The quantity of the product added to the cart.
 * @property Carbon|null              $created_at    The timestamp when the cart item was created.
 * @property Carbon|null              $updated_at    The timestamp when the cart item was last updated.
 *
 * @property-read Product              $product       The associated Product model instance.
 * @property-read \App\Models\User     $user          The associated User model instance.
 * @property-read float                $total_price   The total price for the cart item, calculated as (price + tax_amount) * quantity.
 */
class CartItem extends Model
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
        'user_id',
        'product_id',
        'price',
        'tax_amount',
        'quantity',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * Ensures that certain attributes are automatically cast to appropriate data types when accessed or mutated.
     *
     * @var array
     */
    protected $casts = [
        'price'      => 'float',
        'tax_amount' => 'float',
        'quantity'   => 'integer',
    ];

    /**
     * CartItem constructor.
     *
     * Initializes the model by setting the table name based on the application's configuration.
     * Ensures that the model interacts with the correct database table.
     *
     * @param array $attributes  The model's attributes.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Retrieve the table name from the configuration, defaulting to 'cart_items' if not set.
        $this->table = config('ecommerce.table_names.cart_items', 'cart_items');
    }

    /**
     * Get the product associated with the cart item.
     *
     * Defines an inverse one-to-many relationship between CartItem and Product.
     * Allows access to the product details directly from the cart item.
     *
     * @return BelongsTo  The relationship instance.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            config('ecommerce.models.product', Product::class),
            'product_id'
        );
    }

    /**
     * Get the user who owns the cart item.
     *
     * Defines an inverse one-to-many relationship between CartItem and User.
     * Enables retrieval of user information associated with the cart item.
     *
     * @return BelongsTo  The relationship instance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Accessor for the total price attribute.
     *
     * Calculates the total price for the cart item by adding the tax amount to the price
     * and then multiplying by the quantity. This provides a convenient way to retrieve
     * the total cost associated with this cart item.
     *
     * @return float  The calculated total price.
     */
    public function getTotalPriceAttribute(): float
    {
        return ($this->price + $this->tax_amount) * $this->quantity;
    }
}
