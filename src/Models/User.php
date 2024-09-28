<?php

namespace Davidiwezulu\Ecommerce\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class User
 *
 * This class represents the user model for the e-commerce system. It extends Laravel's
 * `Authenticatable` class, providing built-in authentication capabilities. The model
 * includes basic attributes like name, email, and password, and can be extended with
 * additional relationships, accessors, and mutators as needed.
 *
 * @package Davidiwezulu\Ecommerce\Models
 * @category Model
 * @license MIT
 * @link https://davidiwezulu.co.uk/documentation
 *
 * @property int $id The unique identifier for the user.
 * @property string $name The name of the user.
 * @property string $email The email address of the user.
 * @property string $password The password of the user (hashed).
 * @property Carbon|null $created_at The timestamp when the user was created.
 * @property Carbon|null $updated_at The timestamp when the user was last updated.
 */
class User extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Specifies which attributes can be bulk-assigned to the model, helping to prevent
     * mass-assignment vulnerabilities.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
