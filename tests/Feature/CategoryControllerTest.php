<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_categories_with_product_count(): void
    {
        $category = Category::factory()->create(['name' => 'Periféricos']);
        Product::factory()->count(3)->for($category)->create();

        $response = $this->get('/categories');

        $response->assertOk();
        $response->assertSeeInOrder(['Periféricos', '3']);
    }

    public function test_store_creates_category_with_slug(): void
    {
        $response = $this->post('/categories', ['name' => 'Casa e Jardim']);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Casa e Jardim', 'slug' => 'casa-e-jardim']);
    }

    public function test_store_rejects_duplicated_name(): void
    {
        Category::factory()->create(['name' => 'Livros']);

        $response = $this->post('/categories', ['name' => 'Livros']);

        $response->assertSessionHas('error', 'Categoria já existe.');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_destroy_detaches_and_deactivates_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['active' => true]);

        $this->delete("/categories/{$category->id}");

        $this->assertModelMissing($category);
        $product->refresh();
        $this->assertNull($product->category_id);
        $this->assertFalse($product->active);
    }
}
