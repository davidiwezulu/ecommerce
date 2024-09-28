<?php

namespace Davidiwezulu\Ecommerce\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Davidiwezulu\Ecommerce\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminService
 *
 * Provides administrative functionalities for managing products and inventory within the e-commerce system.
 * This service encapsulates operations such as adding new products, updating existing products, and managing
 * stock levels, ensuring a clean separation of concerns and promoting code reusability.
 *
 * @package    Davidiwezulu\Ecommerce\Services
 * @subpackage AdminService
 * @category   Service
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
class AdminService
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
     * Inventory model instance.
     *
     * Represents the Inventory model used for managing stock levels of products.
     *
     * @var Model
     */
    protected mixed $inventoryModel;

    /**
     * AdminService constructor.
     *
     * Initializes the AdminService by instantiating the ProductRepository and retrieving the Inventory model
     * from the configuration. This setup ensures that the service has the necessary dependencies to perform
     * its operations.
     *
     * @return void
     */
    public function __construct()
    {
        $this->productRepo    = new ProductRepository();
        $this->inventoryModel = Config::get('ecommerce.models.inventory');
    }

    /**
     * Add a new product to the catalog.
     *
     * Creates a new product record in the database using the provided product data. This method delegates
     * the creation process to the ProductRepository, ensuring adherence to the repository pattern.
     *
     * @param array $data An associative array containing product information, including:
     *                    - 'name'        : string, the name of the product.
     *                    - 'sku'         : string|null, the Stock Keeping Unit identifier.
     *                    - 'price'       : float, the price of the product.
     *                    - 'tax_rate'    : float|null, the applicable tax rate.
     *                    - 'description' : string|null, a detailed description of the product.
     *                    - 'image'       : string|null, the URL or path to the product's image.
     *                    - 'inventory_id': int|null, the associated inventory record ID.
     *
     * @return Model The newly created Product model instance.
     *
     * @throws ModelNotFoundException If the associated inventory record does not exist.
     * @throws QueryException               If the database query fails.
     */
    public function addProduct(array $data): Model
    {
        return $this->productRepo->create($data);
    }

    /**
     * Update an existing product.
     *
     * Updates the details of an existing product identified by the given product ID with the new data provided.
     * This method delegates the update process to the ProductRepository, maintaining a clear separation of
     * concerns and adhering to the repository pattern.
     *
     * @param int   $productId The unique identifier of the product to update.
     * @param array $data      An associative array containing the product attributes to update, such as:
     *                          - 'name'        : string, the new name of the product.
     *                          - 'sku'         : string|null, the new SKU identifier.
     *                          - 'price'       : float, the new price of the product.
     *                          - 'tax_rate'    : float|null, the new tax rate.
     *                          - 'description' : string|null, the new description of the product.
     *                          - 'image'       : string|null, the new image URL or path.
     *                          - 'inventory_id': int|null, the new associated inventory record ID.
     *
     * @return Model|null Returns the updated Product model instance if the update was successful,
     *                                                 or null if no matching record was found.
     *
     * @throws \InvalidArgumentException                      If the provided product ID is not a positive integer.
     * @throws ModelNotFoundException If the product to update does not exist.
     * @throws QueryException               If the database query fails.
     */
    public function updateProduct(int $productId, array $data): ?Model
    {
        return $this->productRepo->update($productId, $data);
    }

    /**
     * Update the inventory stock for a product.
     *
     * Adjusts the stock level of a specific product by updating its inventory record. This method
     * either updates the existing inventory record with the new quantity or creates a new inventory
     * record if one does not already exist. Ensures that inventory management is handled consistently.
     *
     * @param int $productId The unique identifier of the product whose inventory is being updated.
     * @param int $quantity The new quantity of the product in stock.
     *
     * @return Model|bool The updated or newly created Inventory model instance.
     *
     */
    public function updateInventory(int $productId, int $quantity): Model|bool
    {
        return $this->inventoryModel::updateOrCreate(
            ['product_id' => $productId],
            ['quantity' => $quantity]
        );
    }
}
