<?php

namespace Database\Factories\Davidiwezulu\Ecommerce\Models;

use Davidiwezulu\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for creating instances of the Product model.
 *
 * This factory defines the default state for the Product model, utilizing Faker to generate
 * realistic and unique data for testing and seeding purposes within the ecommerce system.
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * This property specifies which model the factory is responsible for creating.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * This method returns an array of attributes with fake data that will be used
     * to create new instances of the Product model. Each attribute leverages
     * Faker to ensure realistic and varied data.
     *
     * @return array<string, mixed> The default set of attributes for the Product model.
     */
    public function definition(): array
    {
        return [
            /**
             * The name of the product.
             *
             * Generates a single word representing the product name.
             *
             * @var string
             */
            'name' => $this->faker->word(),

            /**
             * The price of the product.
             *
             * Generates a floating-point number with two decimal places, ranging
             * between 10 and 1000 to simulate realistic product pricing.
             *
             * @var float
             */
            'price' => $this->faker->randomFloat(2, 10, 1000),

            /**
             * The applicable tax rate for the product.
             *
             * Selects a random tax rate from a predefined set of common rates.
             *
             * @var float
             */
            'tax_rate' => $this->faker->randomElement([0.05, 0.1, 0.15, 0.2]),

            /**
             * A brief description of the product.
             *
             * Generates a single sentence providing an overview of the product.
             *
             * @var string
             */
            'description' => $this->faker->sentence(),

            /**
             * The Stock Keeping Unit (SKU) identifier for the product.
             *
             * Generates a unique SKU by replacing placeholders with random uppercase
             * letters, ensuring each product can be distinctly identified.
             *
             * @var string
             */
            'sku' => strtoupper($this->faker->unique()->lexify('SKU-????')),

        ];
    }

    /**
     * Associate an inventory record with the product after it is created.
     *
     * This method attaches an inventory record to a newly created product. If no quantity is provided,
     * a random quantity between 10 and 100 will be generated. This is useful for testing and seeding
     * purposes where products are automatically linked with inventory.
     *
     * @param int|null $quantity Optional quantity of the product to be set in the inventory.
     * @return ProductFactory
     */
    public function withInventory(?int $quantity = null): ProductFactory
    {
        return $this->afterCreating(function (Product $product) use ($quantity) {
            // Create an inventory record for the product with the specified or random quantity.
            $product->inventory()->create([
                'quantity' => $quantity ?? $this->faker->numberBetween(10, 100),
            ]);
        });
    }


}
