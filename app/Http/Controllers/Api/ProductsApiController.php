<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductsApiController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->get('name');
        $offset = $request->get('first', 0);
        $limit = $request->input('rows', 1000);
        $query = Product::query();
        $filters = json_decode($request->input('filters'), true) ?? [];
        $with_resources = $request->input('with_resources', '') === 'true';
        if ($name = Arr::get($filters, 'global.value', '')) {
            $query->where('name', 'like', "%$name%");
        }

        $sort = $request->input('multiSortMeta', []);
        foreach ($sort as $s) {
            $s = json_decode($s, true);
            $field = '';
            if (in_array($s['field'], ['name', 'price', 'descripion'])) {
                $field = $s['field'];
            }
            if (!$field) continue;
            $query->orderBy($field, $s['order'] === 1 ? 'asc' : 'desc');
        }
        if ($with_resources) {
            $query = $query->with('resources');
        }
        return [
            'count' => $query->count(),
            'data' => $query->offset($offset)->limit($limit)->get(),
        ];
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        $product = new Product([
            'name' => $request->post('name'),
            'price' => $request->post('price'),
            'description' => $request->post('description'),
            'stock' => $request->post('stock')
        ]);
        $product->save();
        foreach ($request->post('resources') as $resource) {
            $product->resources()->attach([
                $resource['id'] => ['quantity' => $resource['quantity']]
            ]);
        }
        $images = $request->file('images');
        foreach ($images ?? [] as $index => $image) {
            $path = $image->storeAs('img/products', "product-{$index}-{$product->id}."  . $image->getClientOriginalExtension(), 'public');
            $product->images()->create([
                'name' => "{$product->name} {$index}",
                'file_name' => $path,
            ]);
        }
        DB::commit();
        return ['id' => $product->id];
    }

    public function updateStock(Request $request, Product $product, int $stock)
    {
        DB::beginTransaction();
        $product->stock = $stock;
        $product->save();

        foreach ($request->post('resources') as $resource_to_use) {
            $id = $resource_to_use['resource_id'];
            $quantity = $resource_to_use['quantity'];
            $resource = Resource::find($id);
            if ($resource->stock - $quantity < 0) {
                $quantity = $resource->stock;
            }
            $resource->stock -= $quantity;
            $resource->save();
        }
        DB::commit();
        return ['message' => 'Updated stock'];
    }

    public function mostSold(Request $request)
    {
        $sold = Product::query()
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->groupBy('product_id')
            ->selectRaw('SUM(order_products.quantity) as products_sold, product_id')
            ->orderBy('products_sold', 'desc')
            ->limit(10)
            ->pluck('products_sold', 'product_id')
            ->all();
        $products = [];
        foreach ($sold as $id => $quantity) {
            $product = Product::find($id);
            $product->sold = (int)$quantity;
            $products[] = $product;
        }
        return ['data' => $products];
    }

    public function lowOnStock(Request $request)
    {
        return ['data' => Product::query()->orderBy('stock')->limit(10)->get()];
    }
}
