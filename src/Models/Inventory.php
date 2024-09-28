<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Inventory
 *
 * Represents the inventory details of a product within the e-commerce system.
 * This model encapsulates the quantity of a specific product available in stock.
 * It establishes a relationship with the associated Product model, facilitating
 * seamless data retrieval and manipulation related to product inventory.
 *
 * @package    Davidiwezulu\Ecommerce\Models
 * @subpackage Inventory
 * @category   Model
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @property int          $id           The unique identifier for the inventory record.
 * @property int          $product_id   The unique identifier of the associated product.
 * @property int          $quantity     The quantity of the product available in inventory.
 * @property Carbon|null  $created_at   The timestamp when the inventory record was created.
 * @property Carbon|null  $updated_at   The timestamp when the inventory record was last updated.
 *
 * @property-read Product $product      The associated Product model instance.
 */
class Inventory extends Model
{
    use HasFactory;

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
        'product_id',
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
        'product_id' => 'integer',
        'quantity'   => 'integer',
    ];

    /**
     * Inventory constructor.
     *
     * Initializes the model by setting the table name based on the application's configuration.
     * Ensures that the model interacts with the correct database table.
     *
     * @param array $attributes  The model's attributes.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Retrieve the table name from the configuration, defaulting to 'inventories' if not set.
        $this->table = config('ecommerce.table_names.inventories', 'inventories');
    }

    /**
     * Get the product associated with the inventory.
     *
     * Defines an inverse one-to-one or one-to-many relationship between Inventory and Product.
     * Allows access to the product details directly from the inventory record.
     *
     * @return BelongsTo  The relationship instance.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            config('ecommerce.models.product', Product::class),
            'product_id',
            'id'
        );
    }

    /**
     * Scope a query to only include inventories with a minimum quantity.
     *
     * Useful for retrieving products that are sufficiently stocked.
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
     * Decrease the inventory quantity by a specified amount.
     *
     * Ensures that the inventory does not go negative.
     *
     * @param int $amount  The amount to decrease from the current inventory.
     * @return bool        Indicates whether the operation was successful.
     */
    public function decreaseQuantity(int $amount): bool
    {
        if ($this->quantity < $amount) {
            return false;
        }

        $this->quantity -= $amount;
        return $this->save();
    }

    /**
     * Increase the inventory quantity by a specified amount.
     *
     * @param int $amount  The amount to add to the current inventory.
     * @return bool        Indicates whether the operation was successful.
     */
    public function increaseQuantity(int $amount): bool
    {
        $this->quantity += $amount;
        return $this->save();
    }
}
