<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Notebook Gamer',
            'description' => 'Notebook para jogos',
            'category_id' => Category::factory()->create()->id,
            'type' => 'physical',
            'price' => '1000.00',
            'weight' => '2.5',
            'stock' => '10',
            'active' => '1',
        ], $overrides);
    }

    private function sentEmailsCount(): int
    {
        return Mail::getSymfonyTransport()->messages()->count();
    }

    public function test_index_lists_products_with_final_price_per_type(): void
    {
        Product::factory()->create(['name' => 'Cadeira', 'type' => 'physical', 'price' => 100, 'weight' => 2]);
        Product::factory()->create(['name' => 'Monitor', 'type' => 'physical', 'price' => 1000, 'weight' => 5]);
        Product::factory()->digital()->create(['name' => 'Ebook', 'price' => 100]);
        Product::factory()->service()->create(['name' => 'Mentoria', 'price' => 100]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertSeeInOrder(['Cadeira', 'R$ 127,00'], false);
        $response->assertSee('R$ 1.180,00');
        $response->assertSee('R$ 105,00');
        $response->assertSee('R$ 102,00');
    }

    public function test_index_filters_by_search_term(): void
    {
        Product::factory()->create(['name' => 'Teclado Mecânico']);
        Product::factory()->create(['name' => 'Mouse Sem Fio']);

        $response = $this->get('/products?search=Teclado');

        $response->assertSee('Teclado Mecânico');
        $response->assertDontSee('Mouse Sem Fio');
    }

    public function test_index_filters_low_stock_physical_products(): void
    {
        Product::factory()->create(['name' => 'Quase Acabando', 'stock' => 2]);
        Product::factory()->create(['name' => 'Bem Estocado', 'stock' => 50]);

        $response = $this->get('/products?low_stock=1');

        $response->assertSee('Quase Acabando');
        $response->assertDontSee('Bem Estocado');
    }

    public function test_store_creates_product_with_slug_and_sku(): void
    {
        $this->freezeTime();

        $response = $this->post('/products', $this->validPayload());

        $response->assertRedirect('/products');
        $response->assertSessionHas('success', 'Produto cadastrado com sucesso!');

        $product = Product::sole();
        $this->assertSame('notebook-gamer', $product->slug);
        $this->assertMatchesRegularExpression('/^PHY-'.now()->format('ymd').'-[A-Z0-9]{5}$/', $product->sku);
        $this->assertSame(10, $product->stock);
        $this->assertTrue($product->active);
    }

    public function test_store_appends_suffix_when_slug_already_exists(): void
    {
        Product::factory()->create(['slug' => 'notebook-gamer']);

        $this->post('/products', $this->validPayload());

        $this->assertDatabaseHas('products', ['slug' => 'notebook-gamer-1']);
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $response = $this->post('/products', []);

        $response->assertSessionHasErrors(['name', 'type', 'price']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_rejects_physical_product_without_weight(): void
    {
        $response = $this->post('/products', $this->validPayload(['weight' => '']));

        $response->assertSessionHasErrors(['weight' => 'Produtos físicos precisam de peso.']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_forces_zero_stock_and_no_weight_for_services(): void
    {
        $this->post('/products', $this->validPayload(['type' => 'service', 'stock' => '30']));

        $product = Product::sole();
        $this->assertSame(0, $product->stock);
        $this->assertNull($product->weight);
        $this->assertStringStartsWith('SER-', $product->sku);
    }

    public function test_store_saves_uploaded_image(): void
    {
        Storage::fake('public');

        $this->post('/products', $this->validPayload(['image' => UploadedFile::fake()->image('foto.jpg')]));

        Storage::disk('public')->assertExists(Product::sole()->image_path);
    }

    public function test_store_sends_low_stock_alert_for_physical_product(): void
    {
        $this->post('/products', $this->validPayload(['stock' => '2']));

        $this->assertSame(1, $this->sentEmailsCount());
    }

    public function test_store_does_not_send_alert_when_stock_is_enough(): void
    {
        $this->post('/products', $this->validPayload(['stock' => '20']));

        $this->assertSame(0, $this->sentEmailsCount());
    }

    public function test_show_renders_price_breakdown(): void
    {
        $product = Product::factory()->create(['type' => 'physical', 'price' => 100, 'weight' => 2]);

        $response = $this->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee('R$ 18,00');
        $response->assertSee('R$ 9,00');
        $response->assertSee('R$ 127,00');
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->get('/products/999')->assertNotFound();
    }

    public function test_update_changes_product_and_regenerates_slug(): void
    {
        $product = Product::factory()->create(['name' => 'Antigo', 'slug' => 'antigo']);

        $response = $this->put("/products/{$product->id}", $this->validPayload(['name' => 'Novo Nome']));

        $response->assertRedirect('/products');
        $product->refresh();
        $this->assertSame('Novo Nome', $product->name);
        $this->assertSame('novo-nome', $product->slug);
    }

    public function test_update_rejects_physical_product_without_weight(): void
    {
        $product = Product::factory()->create();

        $response = $this->put("/products/{$product->id}", $this->validPayload(['weight' => '']));

        $response->assertSessionHasErrors(['weight' => 'Produtos físicos precisam de peso.']);
    }

    public function test_destroy_deletes_product_without_stock(): void
    {
        $product = Product::factory()->create(['stock' => 0]);

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertModelMissing($product);
    }

    public function test_destroy_refuses_physical_product_with_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $response = $this->delete("/products/{$product->id}");

        $response->assertSessionHas('error', 'Não é possível excluir um produto com estoque.');
        $this->assertModelExists($product);
    }

    public function test_adjust_stock_adds_units(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post("/products/{$product->id}/stock", ['operation' => 'add', 'quantity' => 5]);

        $this->assertSame(15, $product->refresh()->stock);
    }

    public function test_adjust_stock_refuses_removing_more_than_available(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->post("/products/{$product->id}/stock", ['operation' => 'remove', 'quantity' => 5]);

        $response->assertSessionHas('error', 'Estoque insuficiente.');
        $this->assertSame(2, $product->refresh()->stock);
    }

    public function test_adjust_stock_refuses_non_physical_products(): void
    {
        $product = Product::factory()->digital()->create(['stock' => 0]);

        $response = $this->post("/products/{$product->id}/stock", ['operation' => 'add', 'quantity' => 5]);

        $response->assertSessionHas('error', 'Somente produtos físicos possuem estoque.');
    }

    public function test_adjust_stock_sends_alert_when_stock_drops_below_five(): void
    {
        $product = Product::factory()->create(['stock' => 6]);

        $this->post("/products/{$product->id}/stock", ['operation' => 'remove', 'quantity' => 3]);

        $this->assertSame(1, $this->sentEmailsCount());
    }

    public function test_export_downloads_csv(): void
    {
        $product = Product::factory()->create(['name' => 'Fone', 'price' => 199.9]);

        $response = $this->get('/products/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString("{$product->sku};Fone;", $response->getContent());
        $this->assertStringContainsString('199,90', $response->getContent());
    }
}
