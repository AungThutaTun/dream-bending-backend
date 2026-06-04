<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::withTrashed()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(15);

        return $this->success([
            'products'   => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'origin'      => ['nullable', 'string', 'max:100'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'thumbnail'   => ['nullable', 'string'],
            'badge'       => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name) . '-' . uniqid();

        $product = Product::create($data);

        return $this->created(new ProductResource($product->load('category')), 'Product created');
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success(new ProductResource($product->load('category')));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'origin'      => ['nullable', 'string', 'max:100'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'stock'       => ['sometimes', 'integer', 'min:0'],
            'thumbnail'   => ['nullable', 'string'],
            'badge'       => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
        ]);

        if ($request->filled('name')) {
            $product->slug = Str::slug($request->name) . '-' . $product->id;
        }

        $product->update($request->except('slug'));

        return $this->success(new ProductResource($product->load('category')), 'Product updated');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete(); // Soft delete

        return $this->success(message: 'Product soft deleted');
    }

    public function restore(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return $this->success(new ProductResource($product), 'Product restored');
    }

    // Manage categories
    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'icon'      => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $category = Category::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'icon'      => $request->icon,
            'is_active' => $request->get('is_active', true),
        ]);

        return $this->created($category, 'Category created');
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'icon'      => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if ($request->filled('name')) {
            $category->slug = Str::slug($request->name);
        }

        $category->update($request->all());

        return $this->success($category, 'Category updated');
    }
}
