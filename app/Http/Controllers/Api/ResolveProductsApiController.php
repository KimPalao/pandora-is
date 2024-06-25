<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ResolveProductsApiController extends Controller
{
    public function index(Request $request)
    {
        $product_ids = explode(',', $request->get('products'));
        $quantities = explode(',', $request->get('quantities'));
        $resources = [];
        $products = [];
        foreach ($product_ids as $index => $product_id) {
            $quantity = $quantities[$index];
            $product = Product::find($product_id);
            if (!$product) continue;
            $products[] = $product;
            foreach ($product->resources as $resource) {
                if (!array_key_exists($resource->id, $resources)) {
                    $resources[$resource->id] = [
                        'resource' => $resource,
                        'quantity' => -$resource->stock,
                        'required' => 0,
                    ];
                }
                $resources[$resource->id]['quantity'] += (int)$quantity * $resource->pivot->quantity;
                $resources[$resource->id]['required'] += (int)$quantity * $resource->pivot->quantity;
            }
        }
        foreach ($resources as $resource_id => $resource) {
            if ($resource['quantity'] < 0) $resources[$resource_id]['quantity'] = 0;
        }
        return ['products' => $products, 'resources' => $resources];
    }
}
