<?php

namespace App\Http\Controllers;

use App\Exports\CsvProductExporter;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        if ($request->has('only_active') && $request->only_active == '1') {
            $query->where('active', true);
        }

        if ($request->has('low_stock') && $request->low_stock == '1') {
            $query->where('type', 'physical')->where('stock', '<', 5);
        }

        if ($request->sort == 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($request->sort == 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(10)->withQueryString();

        foreach ($products as $product) {
            if ($product->type == 'physical') {
                $tax = $product->price * 0.18;
                $shipping = $product->weight * 4.5;
                if ($product->price > 500) {
                    $shipping = 0;
                }
                $product->final_price = $product->price + $tax + $shipping;
            } elseif ($product->type == 'digital') {
                $tax = $product->price * 0.05;
                $product->final_price = $product->price + $tax;
            } elseif ($product->type == 'service') {
                $tax = $product->price * 0.02;
                $product->final_price = $product->price + $tax;
            } else {
                $product->final_price = $product->price;
            }

            $product->final_price_formatted = 'R$ '.number_format($product->final_price, 2, ',', '.');
        }

        $categories = Category::orderBy('name')->get();

        $totalProducts = DB::table('products')->count();
        $totalStockValue = DB::table('products')->where('type', 'physical')->sum(DB::raw('price * stock'));
        $lowStockCount = DB::table('products')->where('type', 'physical')->where('stock', '<', 5)->count();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => $totalProducts,
            'totalStockValue' => $totalStockValue,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:physical,digital,service',
            'price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($data['type'] == 'physical' && empty($data['weight'])) {
            return back()->withInput()->withErrors(['weight' => 'Produtos físicos precisam de peso.']);
        }

        if ($data['type'] != 'physical') {
            $data['weight'] = null;
        }

        if ($data['type'] == 'service') {
            $data['stock'] = 0;
        }

        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }
        $data['slug'] = $slug;

        $prefix = strtoupper(substr($data['type'], 0, 3));
        $data['sku'] = $prefix.'-'.date('ymd').'-'.strtoupper(Str::random(5));

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $data['active'] = $request->has('active');
        $data['stock'] = $data['stock'] ?? 0;

        $product = Product::create($data);

        Log::info('Produto criado: '.$product->id.' - '.$product->name);

        if ($product->type == 'physical' && $product->stock < 5) {
            Mail::raw(
                'O produto '.$product->name.' (SKU '.$product->sku.') foi cadastrado com estoque baixo: '.$product->stock.' unidades.',
                function ($message) {
                    $message->to('estoque@empresa.com')->subject('Alerta de estoque baixo');
                }
            );
        }

        return redirect('/products')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (! $product) {
            abort(404);
        }

        if ($product->type == 'physical') {
            $tax = $product->price * 0.18;
            $shipping = $product->weight * 4.5;
            if ($product->price > 500) {
                $shipping = 0;
            }
            $finalPrice = $product->price + $tax + $shipping;
        } elseif ($product->type == 'digital') {
            $tax = $product->price * 0.05;
            $shipping = 0;
            $finalPrice = $product->price + $tax;
        } elseif ($product->type == 'service') {
            $tax = $product->price * 0.02;
            $shipping = 0;
            $finalPrice = $product->price + $tax;
        } else {
            $tax = 0;
            $shipping = 0;
            $finalPrice = $product->price;
        }

        return view('products.show', [
            'product' => $product,
            'tax' => $tax,
            'shipping' => $shipping,
            'finalPrice' => $finalPrice,
        ]);
    }

    public function edit($id)
    {
        $product = Product::find($id);

        if (! $product) {
            abort(404);
        }

        $categories = Category::orderBy('name')->get();

        return view('products.edit', ['product' => $product, 'categories' => $categories]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:physical,digital,service',
            'price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($data['type'] == 'physical' && empty($data['weight'])) {
            return back()->withInput()->withErrors(['weight' => 'Produtos físicos precisam de peso.']);
        }

        if ($data['type'] != 'physical') {
            $data['weight'] = null;
        }

        if ($data['type'] == 'service') {
            $data['stock'] = 0;
        }

        if ($data['name'] != $product->name) {
            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug.'-'.$count;
                $count++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $data['active'] = $request->has('active');
        $data['stock'] = $data['stock'] ?? 0;

        $oldPrice = $product->price;

        $product->update($data);

        Log::info('Produto atualizado: '.$product->id.' - '.$product->name);

        if ($oldPrice != $product->price) {
            Log::info('Preço do produto '.$product->id.' alterado de '.$oldPrice.' para '.$product->price);
        }

        if ($product->type == 'physical' && $product->stock < 5) {
            Mail::raw(
                'O produto '.$product->name.' (SKU '.$product->sku.') está com estoque baixo: '.$product->stock.' unidades.',
                function ($message) {
                    $message->to('estoque@empresa.com')->subject('Alerta de estoque baixo');
                }
            );
        }

        return redirect('/products')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            abort(404);
        }

        if ($product->type == 'physical' && $product->stock > 0) {
            return redirect('/products')->with('error', 'Não é possível excluir um produto com estoque.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        Log::info('Produto excluído: '.$id);

        return redirect('/products')->with('success', 'Produto excluído com sucesso!');
    }

    public function adjustStock(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            abort(404);
        }

        $request->validate([
            'operation' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($product->type != 'physical') {
            return back()->with('error', 'Somente produtos físicos possuem estoque.');
        }

        if ($request->operation == 'add') {
            $product->stock = $product->stock + $request->quantity;
        } else {
            if ($product->stock < $request->quantity) {
                return back()->with('error', 'Estoque insuficiente.');
            }
            $product->stock = $product->stock - $request->quantity;
        }

        $product->save();

        Log::info('Estoque do produto '.$product->id.' ajustado ('.$request->operation.' '.$request->quantity.')');

        if ($product->stock < 5) {
            Mail::raw(
                'O produto '.$product->name.' (SKU '.$product->sku.') está com estoque baixo: '.$product->stock.' unidades.',
                function ($message) {
                    $message->to('estoque@empresa.com')->subject('Alerta de estoque baixo');
                }
            );
        }

        return back()->with('success', 'Estoque atualizado!');
    }

    public function export()
    {
        $products = Product::with('category')->orderBy('name')->get();

        $exporter = new CsvProductExporter;
        $csv = $exporter->toCsv($products);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="produtos-'.date('Y-m-d').'.csv"',
        ]);
    }
}
