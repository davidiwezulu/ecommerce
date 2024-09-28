<?php

namespace Database\Factories\Davidiwezulu\Ecommerce\Models;

use Davidiwezulu\Ecommerce\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Factory for creating User model instances.
 *
 * This factory defines the default state for the User model, including
 * attributes like name, email, email verification timestamp, password, and
 * remember token.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * This method returns an array of attributes that should be populated
     * when creating a new User instance using the factory. It utilizes the
     * Faker library to generate realistic fake data for testing and seeding.
     *
     * @return array<string, mixed> The default state of the User model.
     */
    public function definition(): array
    {
        return [
            /**
             * The user's full name.
             *
             * @var string
             */
            'name' => $this->faker->name(),

            /**
             * The user's unique and safe email address.
             *
             * @var string
             */
            'email' => $this->faker->unique()->safeEmail(),

            /**
             * The timestamp when the user's email was verified.
             *
             * @var Carbon
             */
            'email_verified_at' => now(),

            /**
             * The user's hashed password.
             *
             * @var string
             */
            'password' => bcrypt('password'),

            /**
             * The token used to remember the user.
             *
             * @var string
             */
            'remember_token' => Str::random(10),
        ];
    }
}
