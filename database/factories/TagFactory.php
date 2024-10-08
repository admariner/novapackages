<?php

namespace Database\Factories;

use App\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = $this->faker->sentence();

        return [
            'name' => Str::lower($name),
            'slug' => Str::slug($name),
        ];
    }
}
