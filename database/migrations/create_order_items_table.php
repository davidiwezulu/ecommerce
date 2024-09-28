<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrUpdateOrderItemsTable
 *
 * This migration manages the creation and updating of the `order_items` table within the e-commerce system.
 * It ensures that the table exists with the necessary columns and foreign key constraints.
 * If the table already exists, it adds any missing columns and constraints without affecting existing data.
 *
 * @package    Database\Migrations
 * @subpackage ECommerce
 * @category   Migration
 * @author     David Iwezulu
 * @license    MIT
 * @link       https://davidiwezulu.co.uk/documentation
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method creates the `order_items` table if it does not exist.
     * It defines the necessary columns and sets up foreign key constraints for `order_id` and `product_id`.
     * If the table already exists, it ensures that all required columns and constraints are present.
     *
     * @return void
     */
    public function up()
    {
        // Retrieve the table name from the configuration, defaulting to 'order_items' if not set.
        $tableName = config('ecommerce.table_names.order_items', 'order_items');

        // Check if the table does not exist before creating it.
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                // Primary key: Auto-incrementing ID.
                $table->id();

                // Define the `order_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'order_id')) {
                    $table->unsignedBigInteger('order_id')
                        ->comment('Foreign key referencing orders table');
                }

                // Define the `product_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')
                        ->comment('Foreign key referencing products table');
                }

                // Define the `quantity` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')
                        ->comment('Quantity of the product in the order');
                }

                // Define the `price` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)
                        ->comment('Price of the product at the time of order');
                }

                // Define the `tax_rate` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)
                        ->nullable()
                        ->default(null)
                        ->comment('Applicable tax rate for the order item');
                }

                // Define the `tax_amount` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'tax_amount')) {
                    $table->decimal('tax_amount', 8, 2)
                        ->comment('Calculated tax amount for the order item');
                }

                // Timestamps: `created_at` and `updated_at`.
                $table->timestamps();

                // Foreign key constraint for `order_id` if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'order_id')) {
                    $table->foreign('order_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.orders', 'orders'))
                        ->onDelete('cascade')
                        ->comment('Cascades delete operations to order items when an order is deleted');
                }

                // Foreign key constraint for `product_id` if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Cascades delete operations to order items when a product is deleted');
                }
            });
        } else {
            // If the table exists, modify it to ensure all necessary columns and constraints are present.
            Schema::table($tableName, function (Blueprint $table) {
                // Add `order_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'order_id')) {
                    $table->unsignedBigInteger('order_id')
                        ->comment('Foreign key referencing orders table');
                    $table->foreign('order_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.orders', 'orders'))
                        ->onDelete('cascade')
                        ->comment('Cascades delete operations to order items when an order is deleted');
                }

                // Add `product_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')
                        ->comment('Foreign key referencing products table');
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Cascades delete operations to order items when a product is deleted');
                }

                // Add `quantity` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')
                        ->comment('Quantity of the product in the order');
                }

                // Add `price` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)
                        ->comment('Price of the product at the time of order');
                }

                // Add `tax_rate` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)
                        ->nullable()
                        ->default(null)
                        ->comment('Applicable tax rate for the order item');
                }

                // Add `tax_amount` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'tax_amount')) {
                    $table->decimal('tax_amount', 8, 2)
                        ->comment('Calculated tax amount for the order item');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the `order_items` table if it exists.
     *
     * @return void
     */
    public function down()
    {
        // Retrieve the table name from the configuration, defaulting to 'order_items' if not set.
        $tableName = config('ecommerce.table_names.order_items', 'order_items');

        // Drop the table if it exists.
        Schema::dropIfExists($tableName);
    }
};
