<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrUpdateProductsTable
 *
 * This migration manages the creation and updating of the `products` table within the e-commerce system.
 * It ensures that the table exists with the necessary columns and foreign key constraints.
 * If the table already exists, it adds any missing columns and constraints without affecting existing data.
 *
 * @package    Database\Migrations
 * @subpackage ECommerce
 * @category   Migration
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method creates the `products` table if it does not exist.
     * It defines the necessary columns and sets up a foreign key constraint for `inventory_id`.
     * If the table already exists, it ensures that all required columns and constraints are present.
     *
     * @return void
     */
    public function up()
    {
        // Retrieve the table name from the configuration, defaulting to 'products' if not set.
        $tableName = config('ecommerce.table_names.products', 'products');

        // Check if the table does not exist before creating it.
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                // Primary key: Auto-incrementing ID.
                $table->id();

                // Define the `name` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'name')) {
                    $table->string('name')
                        ->comment('Name of the product');
                }

                // Define the `sku` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'sku')) {
                    $table->string('sku')->unique()
                        ->comment('Unique Stock Keeping Unit identifier for the product');
                }

                // Define the `price` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)
                        ->comment('Price of the product');
                }

                // Define the `tax_rate` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)
                        ->nullable()
                        ->default(null)
                        ->comment('Applicable tax rate for the product');
                }

                // Define the `description` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'description')) {
                    $table->text('description')
                        ->nullable()
                        ->comment('Detailed description of the product');
                }

                // Define the `inventory_id` column if it does not exist.
                if (Schema::hasColumn($table->getTable(), 'inventory_id')) {
                    $table->dropForeign(['inventory_id']);
                    $table->dropColumn('inventory_id');
                }

                // Timestamps: `created_at` and `updated_at`.
                $table->timestamps();

                // Indexes for frequently queried columns.
                $table->index('sku', 'products_sku_index');
            });
        } else {
            // If the table exists, modify it to ensure all necessary columns and constraints are present.
            Schema::table($tableName, function (Blueprint $table) {
                // Add `name` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'name')) {
                    $table->string('name')
                        ->comment('Name of the product');
                }

                // Add `sku` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'sku')) {
                    $table->string('sku')->unique()
                        ->comment('Unique Stock Keeping Unit identifier for the product');
                }

                // Add `price` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)
                        ->comment('Price of the product');
                }

                // Add `tax_rate` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)
                        ->nullable()
                        ->default(null)
                        ->comment('Applicable tax rate for the product');
                }

                // Add `description` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'description')) {
                    $table->text('description')
                        ->nullable()
                        ->comment('Detailed description of the product');
                }

                // Define the `inventory_id` column if it does not exist.
                if (Schema::hasColumn($table->getTable(), 'inventory_id')) {
                    $table->dropForeign(['inventory_id']);
                    $table->dropColumn('inventory_id');
                }

                // Adding indexes to existing columns if they do not exist.
                if (!Schema::hasIndex($table->getTable(), 'products_sku_index')) {
                    $table->index('sku', 'products_sku_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the `products` table if it exists.
     *
     * @return void
     */
    public function down()
    {
        // Retrieve the table name from the configuration, defaulting to 'products' if not set.
        $tableName = config('ecommerce.table_names.products', 'products');

        // Drop the table if it exists.
        Schema::dropIfExists($tableName);
    }
};
