<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrUpdateOrdersTable
 *
 * This migration manages the creation and updating of the `orders` table within the e-commerce system.
 * It ensures that the table exists with the necessary columns and foreign key constraints.
 * If the table already exists, it adds any missing columns and constraints without affecting existing data.
 *
 * @package    Database\Migrations
 * @subpackage ECommerce
 * @category   Migration
 * @license    MIT
 * @link      https://davidiwezulu.co.uk/documentation
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method creates the `orders` table if it does not exist.
     * It defines the necessary columns and sets up a foreign key constraint for `user_id`.
     * If the table already exists, it ensures that all required columns and constraints are present.
     *
     * @return void
     */
    public function up()
    {
        // Retrieve the table name from the configuration, defaulting to 'orders' if not set.
        $tableName = config('ecommerce.table_names.orders', 'orders');

        // Check if the table does not exist before creating it.
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                // Primary key: Auto-incrementing ID.
                $table->id();

                // Define the `user_id` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'user_id')) {
                    $table->unsignedBigInteger('user_id')
                        ->nullable()
                        ->comment('Foreign key referencing users table');
                }

                // Define the `total` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'total')) {
                    $table->decimal('total', 8, 2)
                        ->comment('Total amount for the order');
                }

                // Define the `status` column if it does not exist.
                if (!Schema::hasColumn($table->getTable(), 'status')) {
                    $table->string('status')
                        ->default('pending')
                        ->comment('Current status of the order');
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
            });
        } else {
            // If the table exists, modify it to ensure all necessary columns and constraints are present.
            Schema::table($tableName, function (Blueprint $table) {
                // Add `user_id` column and foreign key if missing.
                if (!Schema::hasColumn($table->getTable(), 'user_id')) {
                    $table->unsignedBigInteger('user_id')
                        ->nullable()
                        ->comment('Foreign key referencing users table');

                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->comment('Sets user_id to null if the referenced user is deleted');
                }

                // Add `total` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'total')) {
                    $table->decimal('total', 8, 2)
                        ->comment('Total amount for the order');
                }

                // Add `status` column if missing.
                if (!Schema::hasColumn($table->getTable(), 'status')) {
                    $table->string('status')
                        ->default('pending')
                        ->comment('Current status of the order');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the `orders` table if it exists.
     *
     * @return void
     */
    public function down()
    {
        // Retrieve the table name from the configuration, defaulting to 'orders' if not set.
        $tableName = config('ecommerce.table_names.orders', 'orders');

        // Drop the table if it exists.
        Schema::dropIfExists($tableName);
    }
};
