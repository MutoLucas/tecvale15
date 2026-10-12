<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::select('
            SELECT categories.*, COUNT(products.id) AS products_count
            FROM categories
            LEFT JOIN products ON products.category_id = categories.id
            GROUP BY categories.id
            ORDER BY categories.name
        ');

        return view('categories.index', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        if (! $request->name || strlen($request->name) > 255) {
            return back()->with('error', 'Nome inválido.');
        }

        if (Category::where('name', $request->name)->exists()) {
            return back()->with('error', 'Categoria já existe.');
        }

        $request->merge(['slug' => Str::slug($request->name)]);

        Category::create($request->all());

        return redirect('/categories')->with('success', 'Categoria criada!');
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (! $category) {
            abort(404);
        }

        foreach (Product::where('category_id', $id)->get() as $product) {
            $product->category_id = null;
            $product->active = false;
            $product->save();
        }

        $category->delete();

        return redirect('/categories')->with('success', 'Categoria removida e produtos desativados.');
    }
}
