<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersApiController extends Controller
{
    public function index(Request $request)
    {
        $offset = $request->input('first', 0);
        $limit = $request->input('rows', 10);
        $query = Order::query()->with('products');
        $filters = json_decode($request->input('filters'), true) ?? [];
        $sort = $request->input('multiSortMeta', []);
        foreach ($sort as $s) {
            $s = json_decode($s, true);
            $field = '';
            if ($s['field'] === 'total' || $s['field'] === 'created_at') {
                $field = $s['field'];
            }
            if (!$field) continue;
            $query->orderBy($field, $s['order'] === 1 ? 'asc' : 'desc');
        }
        $data = ['count' => $query->count(), 'data' => $query->offset($offset)->limit($limit)->get()];
        return $data;
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        $order = new Order(['total' => $request->post('total'), 'created_at' => $request->post('created_at')]);
        $order->save();
        foreach ($request->post('products') as $product) {
            $quantity = $product['quantity'];
            $to_use = $product['to_use'];
            $p = Product::find($product['id']);
            $order->products()->attach([$p->id => ['quantity' => $quantity]]);
            $p->stock -= $to_use;
            $p->save();
        }
        DB::commit();
        return ['id' => $order->id];
    }
}
