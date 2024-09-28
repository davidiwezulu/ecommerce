<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Class Product
 *
 * Represents a product within the e-commerce system. This model encapsulates all relevant
 * details about a product, including its name, SKU, pricing, tax information, description,
 * associated inventory, and more. It establishes a relationship with the Inventory model,
 * facilitating seamless inventory management and data retrieval.
 *
 * @package    Davidiwezulu\Ecommerce\Models
 * @subpackage Product
 * @category   Model
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @property int          $id            The unique identifier for the product.
 * @property string       $name          The name of the product.
 * @property string|null  $sku           The Stock Keeping Unit identifier for the product.
 * @property float        $price         The price of the product.
 * @property float|null   $tax_rate      The applicable tax rate for the product.
 * @property string|null  $description   A detailed description of the product.
 * @property int|null     $inventory_id  The unique identifier of the associated inventory record.
 * @property Carbon|null  $created_at    The timestamp when the product was created.
 * @property Carbon|null  $updated_at    The timestamp when the product was last updated.
 *
 * @property-read Inventory $inventory     The inventory record associated with the product.
 * @property-read Collection|OrderItem[] $orderItems  The order items that include this product.
 */
class Product extends Model
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
        'name',
        'sku',
        'price',
        'tax_rate',
        'description',
        'inventory_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * Ensures that certain attributes are automatically cast to appropriate data types when accessed or mutated.
     *
     * @var array
     */
    protected $casts = [
        'price'        => 'float',
        'tax_rate'     => 'float',
        'inventory_id' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Product constructor.
     *
     * Initializes the model by setting the table name based on the application's configuration.
     * Ensures that the model interacts with the correct database table.
     *
     * @param array $attributes The model's attributes.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Retrieve the table name from the configuration, defaulting to 'products' if not set.
        $this->table = config('ecommerce.table_names.products', 'products');
    }

    /**
     * Get the inventory associated with the product.
     *
     * Defines an inverse one-to-one or one-to-many relationship between Product and Inventory.
     * Allows access to the inventory details directly from the product.
     *
     * @return HasOne The relationship instance.
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(
            config('ecommerce.models.inventory', Inventory::class),
            'product_id',
            'id'
        );
    }

    /**
     * Get the order items that include this product.
     *
     * Defines a one-to-many relationship between Product and OrderItem.
     * Enables retrieval of all order items that include this product.
     *
     * @return HasMany The relationship instance.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(
            config('ecommerce.models.order_item', OrderItem::class),
            'product_id'
        );
    }

    /**
     * Accessor for the formatted price attribute.
     *
     * Formats the price to two decimal places, ensuring consistent display.
     *
     * @return string The formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Mutator for the price attribute.
     *
     * Ensures that the price is stored as a float with two decimal places.
     *
     * @param mixed $value The value to set for the price.
     * @return void
     */
    public function setPriceAttribute(mixed $value): void
    {
        $this->attributes['price'] = round((float)$value, 2);
    }

    /**
     * Update the inventory quantity when a product is sold.
     *
     * Decreases the inventory quantity based on the sold amount.
     *
     * @param int $quantity The quantity sold.
     * @return bool        Indicates whether the update was successful.
     *
     * @throws \Exception If there is insufficient inventory.
     */
    public function sell(int $quantity): bool
    {
        if ($this->inventory && $this->inventory->quantity >= $quantity) {
            $this->inventory->quantity -= $quantity;
            return $this->inventory->save();
        }

        throw new \RuntimeException("Insufficient inventory for product ID {$this->id}");
    }

    /**
     * Increase the inventory quantity when a product is restocked.
     *
     * @param int $quantity The quantity to add.
     * @return bool        Indicates whether the update was successful.
     */
    public function restock(int $quantity): bool
    {
        if ($this->inventory) {
            $this->inventory->quantity += $quantity;
            return $this->inventory->save();
        }

        // Optionally, create a new inventory record if none exists.
        $this->inventory()->create([
            'quantity' => $quantity,
        ]);

        return true;
    }
}
