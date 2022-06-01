<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->realText(100),
            'description' =>  $this->faker->realText(500),
            'body' => $this->faker->realText(20000)
        ];
    }
}
