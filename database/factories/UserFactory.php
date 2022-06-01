<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->imageUrl(),
            'bio' => $this->faker->unique()->realText(255),
            'passwordHash' => Hash::make($this->faker->password),
        ];
    }
}
