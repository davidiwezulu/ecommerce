# 🎉 Laravel E-Commerce Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/davidiwezulu/ecommerce.svg?style=flat-square)](https://packagist.org/packages/davidiwezulu/ecommerce)
![Laravel 8.x](https://img.shields.io/badge/Laravel-8.x-red.svg?style=flat-square&logo=laravel)
![Laravel 9.x](https://img.shields.io/badge/Laravel-9.x-red.svg?style=flat-square&logo=laravel)
![Laravel 10.x](https://img.shields.io/badge/Laravel-10.x-red.svg?style=flat-square&logo=laravel)

## Introduction 

After letting this gem sit in a private repository for way too long, I decided it was time to dust it off, give it a serious makeover, and finally share it with everyone. What started as a personal side project has now evolved into a full-fledged e-commerce package 🚀.


## Overview

An extensible Laravel package providing robust e-commerce functionalities, including cart management, order processing, admin operations, flexible tax calculations, and seamless payment gateway integration (Stripe and PayPal). Designed without a predefined UI to offer maximum flexibility, allowing integration with any Laravel backend, controllers, or views.

## Laravel Version Compatibility

This package is compatible with the following Laravel versions:

| Laravel Version | Supported |
|-----------------|-----------|
| 8.x             | ✅ Yes    |
| 9.x             | ✅ Yes    |
| 10.x            | ✅ Yes    |

## Requirements

- **PHP**: >= 8.0
- **Laravel**: 8.x, 9.x, or 10.x
- **Database**: MySQL, PostgreSQL, SQLite, or any Laravel-supported database.


## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Important Note](#important-note)
    - [Currency Settings](#currency-settings)
    - [Tax Settings](#tax-settings)
    - [Payment Gateways](#payment-gateways)
        - [Stripe Configuration](#stripe-configuration)
        - [PayPal Configuration](#paypal-configuration)
    - [Custom Models](#custom-models)
    - [Database Migrations](#database-migrations)
- [Usage](#usage)
    - [Cart Management](#cart-management)
        - [Adding or Updating Cart Items](#adding-or-updating-cart-items)
        - [Removing Cart Items](#removing-cart-items)
        - [Retrieving Cart Items](#retrieving-cart-items)
        - [Clearing the Cart](#clearing-the-cart)
    - [Admin Operations](#admin-operations)
        - [Adding a New Product with Tax Rate](#adding-a-new-product-with-tax-rate)
        - [Updating an Existing Product](#updating-an-existing-product)
        - [Updating Inventory](#updating-inventory)
    - [Order Processing](#order-processing)
        - [Stripe Payment Example](#stripe-payment-example)
        - [PayPal Payment Example](#paypal-payment-example)
    - [Tax Calculations](#tax-calculations)
- [Extending the Package](#extending-the-package)
    - [Custom Models](#custom-models-1)
        - [Extend Package Models (Optional)](#extend-package-models-optional)
    - [Adding Custom Payment Gateways](#adding-custom-payment-gateways)
- [License](#license)
- [Conclusion](#conclusion)

## Features

- **Cart Management**: Add, update, remove, and retrieve cart items with tax calculations.
- **Order Processing**: Create, process, and manage orders with tax-inclusive totals.
- **Admin Operations**: Create and manage products, update inventory, and set specific tax rates per product.
- **Payment Gateway Integration**: Supports Stripe and PayPal, with options for customization.
- **Flexible Tax System**: Configure default tax rates, specify per-product tax rates, and choose whether taxes are included in prices.
- **Currency Configuration**: Define default currency symbol and code.
- **Model Customization**: Extend and override default models through configuration.
- **Extensibility**: Implement custom payment gateways by adhering to the provided interface.
- **Payment Status Handling**: Handle successful and failed payments with appropriate responses.
- **Automatic Inventory Tracking**: Automatically updates product inventory levels upon successful order placements.
- **Seamless Integration**: Designed to integrate with any Laravel application without enforcing any specific frontend or UI.

## Installation

### Step 1: Install the Package

Use Composer to install the package into your Laravel application:

```bash
composer require davidiwezulu/ecommerce
```
### Step 2: Publish Configuration and Migrations

**Important:** Before running migrations, ensure you have configured the package according to your application's needs. This includes setting up currency, tax settings, payment gateways, and customizing models if necessary.

Publish the package's configuration file and migrations:

```bash
php artisan vendor:publish --provider="Davidiwezulu\Ecommerce\EcommerceServiceProvider"
```

### Step 3: Configure the Package

Edit the `config/ecommerce.php` file to suit your application's requirements. Ensure all configurations are set correctly before proceeding to migrations.

### Step 4: Run Migrations

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

### Configuration

The package provides a configuration file located at `config/ecommerce.php`, allowing you to customize various aspects.

#### Important Note

Configure the package before running migrations. This ensures that your custom settings, such as table names and model classes, are correctly applied during the migration process.

#### Currency Settings

Set your application's default currency in the `.env` file:

```env
CURRENCY_SYMBOL=£
CURRENCY_CODE=GBP
```
Or directly in config/ecommerce.php:

```'currency' => [
    'symbol' => env('CURRENCY_SYMBOL', '£'),
    'code'   => env('CURRENCY_CODE', 'GBP'),
],
```
#### Tax Settings

Configure tax settings in the .env file:

```
TAX_RATE=0.2  # 20% default tax rate
TAX_INCLUDED_IN_PRICES=false  # Are taxes included in product prices?
```
Or directly in config/ecommerce.php:

```
'tax' => [
    'default_rate' => env('TAX_RATE', 0.0),
    'included_in_prices' => env('TAX_INCLUDED_IN_PRICES', false),
],
```

### Payment Gateways
#### Stripe Configuration

In your .env file:
``` 
STRIPE_SECRET_KEY=your-stripe-secret-key
```
In config/ecommerce.php:
```allykeynamelanguage
'payment_gateways' => [
    'stripe' => [
        'class'      => \Davidiwezulu\Ecommerce\Payments\StripeGateway::class,
        'secret_key' => env('STRIPE_SECRET_KEY'),
    ],
    // ...
],

```
#### PayPal Configuration
In your .env file:
```
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_SECRET=your-paypal-secret
PAYPAL_MODE=sandbox  # Use 'live' for production

```
In config/ecommerce.php:
```
'payment_gateways' => [
    'paypal' => [
        'class'     => \Davidiwezulu\Ecommerce\Payments\PayPalGateway::class,
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SECRET'),
        'mode'      => env('PAYPAL_MODE', 'sandbox'),
    ],
    // ...
],
```
### Custom Models
You can override the default models used by the package in config/ecommerce.php:

```
'models' => [
    'product'     => App\Models\Product::class,
    'inventory'   => App\Models\Inventory::class,
    'order'       => App\Models\Order::class,
    'order_item'  => App\Models\OrderItem::class,
    'cart_item'   => App\Models\CartItem::class,
],

```
#### Database Migrations
The package provides migrations for creating necessary database tables. If you've customized table names or models, ensure that these are correctly set in the configuration before running migrations.

Important: Always back up your database before running new migrations, especially in production environments.

## Usage

### Cart Management

Manage cart items using the `Cart` facade.

#### Adding or Updating Cart Items

```php
use Davidiwezulu\Ecommerce\Facades\Cart;

Cart::addOrUpdate($productId, $quantity);
```

#### Removing Cart Items
```
Cart::remove($productId);
```

#### Retrieving Cart Items
```
$items = Cart::items();

foreach ($items as $item) {
    echo 'Product: ' . $item->product->name . PHP_EOL;
    echo 'Price: ' . $item->price . PHP_EOL;
    echo 'Tax Amount: ' . $item->tax_amount . PHP_EOL;
    echo 'Quantity: ' . $item->quantity . PHP_EOL;
    echo 'Total Price: ' . $item->total_price . PHP_EOL; // Uses getTotalPriceAttribute()
    echo '---' . PHP_EOL;
}

```

#### Clearing the Cart
```
Cart::clear();
```
### Admin Operations
Admins can manage products and inventory using the Admin facade.

#### Adding a New Product with Tax Rate
```
use Davidiwezulu\Ecommerce\Facades\Admin;

$productData = [
    'name'        => 'Product Name',
    'price'       => 100.00,
    'tax_rate'    => 0.15, // 15% tax rate
    'description' => 'Product Description',
    'sku'         => 'SKU001',
];

$product = Admin::addProduct($productData);
```
#### Updating an Existing Product
```
$productData = [
    'name'        => 'Updated Product Name',
    'price'       => 120.00,
    'tax_rate'    => 0.18, // Updated tax rate
    'description' => 'Updated Description',
];

$product = Admin::updateProduct($productId, $productData);
```
#### Updating Inventory
``` 
$quantity = 50; // New stock quantity
Admin::updateInventory($productId, $quantity);
```
### Order Processing
Use the Order facade to create orders and process payments.

#### Stripe Payment Example

``` 
use Davidiwezulu\Ecommerce\Facades\Order;
use Illuminate\Support\Facades\Auth;
use Davidiwezulu\Ecommerce\Facades\Cart;

$paymentDetails = [
    'gateway' => 'stripe',
    'token'   => 'stripe-token', // Token from Stripe.js or Checkout
];

$cartItems = Cart::items()->toArray();

try {
    $order = Order::create(Auth::id(), $cartItems, $paymentDetails);
    Cart::clear();
    return redirect()->route('order.success')->with('message', 'Order created successfully!');
} catch (\Exception $e) {
    return redirect()->route('order.failed')->with('error', 'Payment failed: ' . $e->getMessage());
}
```
#### PayPal Payment Example
#### Step 1: Initiate PayPal Payment
``` 
use Davidiwezulu\Ecommerce\Facades\Order;
use Illuminate\Support\Facades\Auth;

$paymentDetails = [
    'gateway'    => 'paypal',
    'return_url' => route('paypal.return'),
    'cancel_url' => route('paypal.cancel'),
];

$cartItems = Cart::items()->toArray();

try {
    // This will redirect the user to PayPal for payment approval
    return Order::create(Auth::id(), $cartItems, $paymentDetails);
} catch (\Exception $e) {
    return redirect()->route('order.failed')->with('error', 'Payment initiation failed: ' . $e->getMessage());
}
```
#### Step 2: Handle PayPal Return (After User Approval)
Create routes in your web.php:

``` 
Route::get('/paypal/return', [PaymentController::class, 'handlePayPalReturn'])->name('paypal.return');
Route::get('/paypal/cancel', [PaymentController::class, 'handlePayPalCancel'])->name('paypal.cancel');
```
Implement the controller methods:
``` 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Davidiwezulu\Ecommerce\Services\OrderService;
use Davidiwezulu\Ecommerce\Facades\Cart;

class PaymentController extends Controller
{
    public function handlePayPalReturn(Request $request)
    {
        $paymentId = $request->get('paymentId');
        $payerId   = $request->get('PayerID');
        $userId    = Auth::id();
        $cartItems = Cart::items()->toArray();

        try {
            $orderService = new OrderService();
            $order = $orderService->executePayPalPayment($paymentId, $payerId, $userId, $cartItems);
            Cart::clear();
            return redirect()->route('order.success')->with('message', 'Order created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('order.failed')->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function handlePayPalCancel()
    {
        return redirect()->route('order.cancelled')->with('message', 'Payment was cancelled.');
    }
}

```
### Tax Calculations
The package supports flexible tax calculations, including per-product tax rates and configurable default tax rates.

#### Adding a Product with a Specific Tax Rate
``` 
use Davidiwezulu\Ecommerce\Facades\Admin;

$productData = [
    'name'        => 'Taxed Product',
    'price'       => 200.00,
    'tax_rate'    => 0.10, // 10% tax rate
    'description' => 'A product with a specific tax rate',
    'sku'         => 'TP-002',
];

$product = Admin::addProduct($productData);
```
#### Calculating Tax in Cart Items
When you add or update items in the cart, the tax amount is automatically calculated based on the product's tax rate or the default tax rate.
``` 
use Davidiwezulu\Ecommerce\Facades\Cart;

// Add product to cart
Cart::addOrUpdate($productId, $quantity);

// Retrieve cart items with tax details
$items = Cart::items();

foreach ($items as $item) {
    echo 'Product: ' . $item->product->name . PHP_EOL;
    echo 'Price: ' . $item->price . PHP_EOL;
    echo 'Tax Amount: ' . $item->tax_amount . PHP_EOL;
    echo 'Total Price (incl. Tax): ' . $item->total_price . PHP_EOL;
}
```

### Extending the Package

#### Custom Models

**Benefit of Custom Models:** Defining models in the configuration allows you to override or extend the default models provided by the package with your own custom models. This provides flexibility to add custom methods, relationships, and business logic specific to your application.

#### Extend Package Models (Optional)

You can extend the package's default models if you want to retain the base functionality while adding your customizations.

**Example:**

```php
namespace App\Models;

use Davidiwezulu\Ecommerce\Models\Product as BaseProduct;

class Product extends BaseProduct
{
    // Add your customizations here

    /**
     * Example of adding a new relationship.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Example of adding a custom method.
     */
    public function calculateDiscountedPrice()
    {
        // Implement your discount logic here
    }
}
```
### Update Configuration:
In config/ecommerce.php:

``` 
'models' => [
    'product' => App\Models\Product::class,
    // Other models...
],
```
#### Explanation:
By extending BaseProduct, you retain all the original functionality provided by the package. You can add new relationships, methods, accessors, and mutators in your custom Product model. The package will use your custom model throughout its operations.

## Adding Custom Payment Gateways
You can add new payment gateways by implementing the PaymentGatewayInterface.

Create a Custom Gateway Class
``` 
namespace App\Payments;

use Davidiwezulu\Ecommerce\Payments\PaymentGatewayInterface;

class CustomGateway implements PaymentGatewayInterface
{
    public function charge($amount, $paymentDetails)
    {
        // Implement charge logic
    }

    public function execute($paymentId, $payerId)
    {
        // Implement execute logic (if needed)
    }

    public function refund($transactionId)
    {
        // Implement refund logic
    }
}
```

### Register the Custom Gateway
In config/ecommerce.php:

``` 
'payment_gateways' => [
    // Existing gateways...
    'custom_gateway' => [
        'class' => \App\Payments\CustomGateway::class,
        // Additional configuration...
    ],
],
```
## License
This package is open-sourced software licensed under the MIT license.

### Conclusion

The Laravel E-commerce package provides a comprehensive solution for implementing e-commerce functionality in your Laravel application. With features like flexible tax calculations, seamless payment gateway integration, and customizable models, it offers a solid foundation for building robust online stores.

**Important:** Remember to configure the package appropriately before running migrations to ensure a smooth integration with your application.

For any questions or contributions, feel free to open an issue or submit a pull request on GitHub.

Thank you for choosing the Laravel E-commerce package!

