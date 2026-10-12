<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(3)).'-'.fake()->unique()->numerify('#####'),
            'description' => fake()->sentence(12),
            'type' => 'physical',
            'price' => fake()->randomFloat(2, 10, 2000),
            'weight' => fake()->randomFloat(3, 0.1, 15),
            'stock' => fake()->numberBetween(0, 100),
            'active' => true,
        ];
    }

    public function digital(): static
    {
        return $this->state(fn (): array => ['type' => 'digital', 'weight' => null]);
    }

    public function service(): static
    {
        return $this->state(fn (): array => ['type' => 'service', 'weight' => null, 'stock' => 0]);
    }
}
