<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        Product::factory()->count(15)->recycle($categories)->create();
        Product::factory()->count(5)->digital()->recycle($categories)->create();
        Product::factory()->count(3)->service()->recycle($categories)->create();
    }
}
