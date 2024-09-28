<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Order
 *
 * Represents a customer's order within the e-commerce system.
 * This model encapsulates the details of an order, including its total amount,
 * status, and associations with the user who placed the order and the items it contains.
 * It establishes relationships with the associated User and OrderItem models,
 * facilitating seamless data retrieval and manipulation related to orders.
 *
 * @package    Davidiwezulu\Ecommerce\Models
 * @subpackage Order
 * @category   Model
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 *
 * @property int          $id         The unique identifier for the order.
 * @property int|null     $user_id    The unique identifier of the user who placed the order.
 * @property float        $total      The total amount for the order.
 * @property string       $status     The current status of the order (e.g., 'pending', 'processing', 'completed', 'cancelled').
 * @property Carbon|null  $created_at The timestamp when the order was created.
 * @property Carbon|null  $updated_at The timestamp when the order was last updated.
 *
 * @property-read \App\Models\User      $user   The user who placed the order.
 * @property-read Collection|OrderItem[] $items   The items associated with the order.
 */
class Order extends Model
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
        'total',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * Ensures that certain attributes are automatically cast to appropriate data types when accessed or mutated.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'total'   => 'float',
        'status'  => 'string',
    ];

    /**
     * Order constructor.
     *
     * Initializes the model by setting the table name based on the application's configuration.
     * Ensures that the model interacts with the correct database table.
     *
     * @param array $attributes The model's attributes.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Retrieve the table name from the configuration, defaulting to 'orders' if not set.
        $this->table = config('ecommerce.table_names.orders', 'orders');
    }

    /**
     * Get the user who placed the order.
     *
     * Defines an inverse one-to-many relationship between Order and User.
     * Allows access to the user details directly from the order.
     *
     * @return BelongsTo The relationship instance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get the items associated with the order.
     *
     * Defines a one-to-many relationship between Order and OrderItem.
     * Enables retrieval of all items that belong to the order.
     *
     * @return HasMany The relationship instance.
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            config('ecommerce.models.order_item', OrderItem::class),
            'order_id'
        );
    }

    /**
     * Scope a query to only include orders with a specific status.
     *
     * Useful for retrieving orders based on their current status.
     *
     * @param Builder $query  The Eloquent query builder.
     * @param string                                 $status The status to filter orders by.
     * @return Builder           The modified query builder.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calculate the total amount for the order by summing the totals of all associated items.
     *
     * This accessor provides a dynamic way to retrieve the computed total of the order,
     * ensuring that it accurately reflects the current state of the order items.
     *
     * @return float The calculated total amount for the order.
     */
    public function getCalculatedTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => ($item->price + $item->tax_amount) * $item->quantity);
    }

    /**
     * Update the status of the order.
     *
     * Provides a convenient method to change the order's status while ensuring business rules are enforced.
     *
     * @param string $newStatus The new status to set for the order.
     * @return bool             Indicates whether the status update was successful.
     *
     * @throws \InvalidArgumentException If the provided status is invalid.
     */
    public function updateStatus(string $newStatus): bool
    {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        $this->status = $newStatus;
        return $this->save();
    }
}
