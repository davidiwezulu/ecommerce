<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E-commerce Models
    |--------------------------------------------------------------------------
    |
    | Specify custom models to use throughout the package. This allows you
    | to extend or replace the default models with your own implementations.
    |
    */

    'models' => [
        'product'     => Davidiwezulu\Ecommerce\Models\Product::class,
        'inventory'   => Davidiwezulu\Ecommerce\Models\Inventory::class,
        'order'       => Davidiwezulu\Ecommerce\Models\Order::class,
        'order_item'  => Davidiwezulu\Ecommerce\Models\OrderItem::class,
        'cart_item'   => Davidiwezulu\Ecommerce\Models\CartItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the package models. This is helpful
    | if you have existing tables or prefer different naming conventions.
    |
    */

    'table_names' => [
        'products'      => 'products',
        'inventories'   => 'inventories',
        'orders'        => 'orders',
        'order_items'   => 'order_items',
        'cart_items'    => 'cart_items',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Define the default currency symbol and code for your application.
    |
    */

    'currency' => [
        'symbol' => env('CURRENCY_SYMBOL', '£'),
        'code'   => env('CURRENCY_CODE', 'GBP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    |
    | Configure tax settings for your application. You can set a default tax rate
    | and specify whether prices include tax.
    |
    */

    'tax' => [
        'default_rate' => env('TAX_RATE', 0.0), // Default tax rate (e.g., 0.2 for 20%)
        'included_in_prices' => env('TAX_INCLUDED_IN_PRICES', false), // Are taxes included in product prices?
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Configure the payment gateways you want to use with the package.
    | You can add custom gateways by implementing the PaymentGatewayInterface.
    |
    */

    'payment_gateways' => [
        'stripe' => [
            'class'      => \Davidiwezulu\Ecommerce\Payments\StripeGateway::class,
            'secret_key' => env('STRIPE_SECRET_KEY'),
        ],
        'paypal' => [
            'class'     => \Davidiwezulu\Ecommerce\Payments\PayPalGateway::class,
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret'    => env('PAYPAL_SECRET'),
            'mode'      => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'
        ],
    ],

];
