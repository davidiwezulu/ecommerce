<?php

namespace Davidiwezulu\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ProductRepository
 *
 * Handles data access and operations for the Product model within the e-commerce system.
 * This repository abstracts the data layer, providing a clean interface for interacting
 * with product data. It encapsulates common CRUD (Create, Read, Update, Delete) operations,
 * promoting code reusability and maintainability.
 *
 * @package    Davidiwezulu\Ecommerce\Repositories
 * @subpackage ProductRepository
 * @category   Repository
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class ProductRepository
{
    /**
     * Product model instance.
     *
     * Represents the Product model used for database interactions.
     *
     * @var Model
     */
    protected mixed $productModel;

    /**
     * ProductRepository constructor.
     *
     * Initializes the repository by retrieving the Product model class from the configuration.
     * This allows for flexibility in swapping out the Product model if needed without altering
     * the repository's implementation.
     *
     * @return void
     */
    public function __construct()
    {
        $this->productModel = Config::get('ecommerce.models.product');
    }

    /**
     * Find a product by its ID.
     *
     * Retrieves a single product record from the database using its unique identifier.
     *
     * @param int $productId The unique identifier of the product to retrieve.
     *
     * @return Model|null Returns the Product model instance if found,
     *                                                 or null if no matching record exists.
     *
     * @throws \InvalidArgumentException If the provided product ID is not a positive integer.
     */
    public function find(int $productId): ?Model
    {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Product ID must be a positive integer.');
        }

        return $this->productModel::find($productId);
    }

    /**
     * Create a new product.
     *
     * Inserts a new product record into the database with the provided data.
     *
     * @param array $data An associative array containing the product attributes, such as:
     *                    - 'name'        : string, the name of the product.
     *                    - 'sku'         : string|null, the Stock Keeping Unit identifier.
     *                    - 'price'       : float, the price of the product.
     *                    - 'tax_rate'    : float|null, the applicable tax rate.
     *                    - 'description' : string|null, a detailed description of the product.
     *                    - 'inventory_id': int|null, the associated inventory record ID.
     *
     * @return Model The newly created Product model instance.
     *
     * @throws ModelNotFoundException If the associated inventory record does not exist.
     * @throws QueryException               If the database query fails.
     */
    public function create(array $data): Model
    {
        return $this->productModel::create($data);
    }

    /**
     * Update an existing product.
     *
     * Updates the product record identified by the given product ID with the new data provided.
     *
     * @param int   $productId The unique identifier of the product to update.
     * @param array $data      An associative array containing the product attributes to update, such as:
     *                          - 'name'        : string, the new name of the product.
     *                          - 'sku'         : string|null, the new SKU identifier.
     *                          - 'price'       : float, the new price of the product.
     *                          - 'tax_rate'    : float|null, the new tax rate.
     *                          - 'description' : string|null, the new description of the product.
     *                          - 'inventory_id': int|null, the new associated inventory record ID.
     *
     * @return Model|null Returns the updated Product model instance if the update was successful,
     *                                                 or null if no matching record was found.
     *
     * @throws \InvalidArgumentException If the provided product ID is not a positive integer.
     * @throws ModelNotFoundException If the product to update does not exist.
     * @throws QueryException               If the database query fails.
     */
    public function update(int $productId, array $data): ?Model
    {
        $product = $this->find($productId);
        if ($product) {
            $product->update($data);
        }

        return $product;
    }

    /**
     * Delete a product by its ID.
     *
     * Removes the product record identified by the given product ID from the database.
     *
     * @param int $productId The unique identifier of the product to delete.
     *
     * @return bool Returns true if the deletion was successful, false otherwise.
     *
     * @throws \InvalidArgumentException If the provided product ID is not a positive integer.
     * @throws ModelNotFoundException If the product to delete does not exist.
     * @throws QueryException               If the database query fails.
     */
    public function delete(int $productId): bool
    {
        $product = $this->find($productId);
        if ($product) {
            return $product->delete();
        }

        return false;
    }

    /**
     * Retrieve all products.
     *
     * Fetches all product records from the database.
     *
     * @return Collection|Model[] A collection of Product model instances.
     *
     * @throws QueryException If the database query fails.
     */
    public function all(): array|Collection
    {
        return $this->productModel::all();
    }
}
