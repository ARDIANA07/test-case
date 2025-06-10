<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('ProductList');
    }
    public function create()
    {
        $products = Product::all();

        return Inertia::render('ProductList', [
            'products' => $products,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array|max:5',
            'products.*.name' => 'required|string|max:255',
            'products.*.description' => 'required|string|max:1000',
            'products.*.image' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        foreach ($request->products as $productData) {
            $imageFileName = null;

            if (isset($productData['image']) && is_file($productData['image'])) {
                $image = $productData['image'];
                $path = $image->store('images', 'public');
                $imageFileName = basename($path);
            }

            Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'image' => $imageFileName,
            ]);
        }

        return redirect()->route('products.create')->with('success', 'Produk berhasil disimpan.');
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Hapus gambar jika ada
        if ($product->image && Storage::disk('public')->exists('images/' . $product->image)) {
            Storage::disk('public')->delete('images/' . $product->image);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }
}