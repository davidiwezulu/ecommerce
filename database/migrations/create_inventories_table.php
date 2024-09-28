<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrUpdateInventoriesTable
 *
 * This migration manages the creation and updating of the `inventories` table within the e-commerce system.
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
     * This method creates the `inventories` table if it does not exist.
     * It defines the necessary columns and sets up a foreign key constraint for `product_id`.
     * If the table already exists, it ensures that all required columns and constraints are present.
     *
     * @return void
     */
    public function up()
    {
        // Retrieve the table name from the configuration, defaulting to 'inventories' if not set.
        $tableName = config('ecommerce.table_names.inventories', 'inventories');

        // Check if the table does not exist before creating it.
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                // Primary key: Auto-incrementing ID.
                $table->id();

                // Define the `product_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')->unique()
                        ->comment('Foreign key referencing products table');
                }

                // Define the `quantity` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')->default(0)
                        ->comment('Quantity of the product in inventory');
                }

                // Timestamps: `created_at` and `updated_at`.
                $table->timestamps();

                // Foreign key constraint for `product_id` if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Deletes inventory record if the referenced product is deleted');
                }
            });
        } else {
            // If the table exists, modify it to ensure all necessary columns and constraints are present.
            Schema::table($tableName, function (Blueprint $table) {
                // Add `product_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'product_id')) {
                    $table->unsignedBigInteger('product_id')->unique()
                        ->comment('Foreign key referencing products table');
                    $table->foreign('product_id')
                        ->references('id')
                        ->on(config('ecommerce.table_names.products', 'products'))
                        ->onDelete('cascade')
                        ->comment('Deletes inventory record if the referenced product is deleted');
                }

                // Add `quantity` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'quantity')) {
                    $table->integer('quantity')->default(0)
                        ->comment('Quantity of the product in inventory');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the `inventories` table if it exists.
     *
     * @return void
     */
    public function down()
    {
        // Retrieve the table name from the configuration, defaulting to 'inventories' if not set.
        $tableName = config('ecommerce.table_names.inventories', 'inventories');

        // Drop the table if it exists.
        Schema::dropIfExists($tableName);
    }
};
