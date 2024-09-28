<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrUpdateCartItemsTable
 *
 * This migration handles the creation and updating of the `cart_items` table within the e-commerce system.
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
     * This method creates the `cart_items` table if it does not exist.
     * It defines the necessary columns and sets up foreign key constraints for `user_id` and `product_id`.
     * If the table already exists, it ensures that all required columns and constraints are present.
     *
     * @return void
     */
    public function up()
    {
        // Retrieve the table name from the configuration, defaulting to 'cart_items' if not set.
        $tableName = config('ecommerce.table_names.cart_items', 'cart_items');

        // Check if the table does not exist before creating it.
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                // Primary key: Auto-incrementing ID.
                $table->id();

                // Define the `user_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->comment('Foreign key referencing users table');
                }

                // Define the `product_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')->comment('Foreign key referencing products table');
                }

                // Define the `price` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)->comment('Price of the product at the time of addition to cart');
                }

                // Define the `quantity` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')->comment('Quantity of the product in the cart');
                }

                // Define the `tax_amount` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'tax_amount')) {
                    $table->decimal('tax_amount', 8, 2)->comment('Applicable tax amount for the cart item');
                }

                // Timestamps: `created_at` and `updated_at`.
                $table->timestamps();

                // Foreign key constraint for `user_id` if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'user_id')) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->comment('Sets user_id to null if the referenced user is deleted');
                }

                // Foreign key constraint for `product_id` if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Deletes cart items if the referenced product is deleted');
                }
            });
        } else {
            // If the table exists, modify it to ensure all necessary columns and constraints are present.
            Schema::table($tableName, function (Blueprint $table) {
                // Add `user_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->comment('Foreign key referencing users table');
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->comment('Sets user_id to null if the referenced user is deleted');
                }

                // Add `product_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')->comment('Foreign key referencing products table');
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Deletes cart items if the referenced product is deleted');
                }

                // Add `quantity` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')->comment('Quantity of the product in the cart');
                }

                // Add `price` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'price')) {
                    $table->decimal('price', 8, 2)->comment('Price of the product at the time of addition to cart');
                }

                // Add `tax_amount` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'tax_amount')) {
                    $table->decimal('tax_amount', 8, 2)->comment('Applicable tax amount for the cart item');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the `cart_items` table if it exists.
     *
     * @return void
     */
    public function down()
    {
        // Retrieve the table name from the configuration, defaulting to 'cart_items' if not set.
        $tableName = config('ecommerce.table_names.cart_items', 'cart_items');

        // Drop the table if it exists.
        Schema::dropIfExists($tableName);
    }
};
