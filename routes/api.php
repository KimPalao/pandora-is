<?php

use App\Models\Bag;
use App\Models\BagImage;
use App\Models\BagMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Resource;
use App\Models\Sale;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/inventory', function (Request $request) {
    $offset = $request->input('first', 0);
    $limit = $request->input('rows', 10);
    $query = Bag::with('latestMovement');
    $filters = json_decode($request->input('filters'), true) ?? [];
    if ($latest_movement = $filters['latest_movement.to_site']['value'] ?? null) {
        $query->whereHas('latestMovement', function ($query) use ($latest_movement) {
            foreach ($latest_movement as $index => $movement) {
                if ($index === 0) {
                    $query->where('to', '=', $movement['id']);
                } else {
                    $query->orWhere('to', '=', $movement['id']);
                }
            }
            return $query;
        });
    }
    if (!is_null($is_sold = $filters['is_sold']['value'] ?? null)) {
        $query->where('is_sold', $is_sold);
    }
    $sort = $request->input('multiSortMeta', []);
    foreach ($sort as $s) {
        $s = json_decode($s, true);
        $field = '';
        if ($s['field'] === 'price' || $s['field'] === 'name') {
            $field = $s['field'];
        }
        if (!$field) continue;
        $query->orderBy($field, $s['order'] === 1 ? 'asc' : 'desc');
    }
    $data = ['count' => $query->count(), 'data' => $query->offset($offset)->limit($limit)->get()];
    return $data;
});
Route::get('/bag/image/{id}/{index}', function ($id, $index) {
    $image = BagImage::whereBagId($id)->limit(1)->offset($index)->first();
    return response()->file(public_path($image->file_name));
});
Route::get('/bag/movement/{id}', function ($id) {
    return ['data' => BagMovement::whereBagId($id)->with(['toSite', 'fromSite'])->orderBy('datetime')->get()->all()];
});
Route::get('/bag/{id}', function (int $id) {
    return ['data' => Bag::whereId($id)->with('images')->with('sale')->with('latestMovement')->first()];
});
Route::get('/sites', function () {
    return ['data' => Site::all()];
});
Route::put('/bag/{bag_id}/movement', function (Request $request, int $bag_id) {
    try {
        return DB::transaction(function () use ($request, $bag_id) {
            $site_id = $request->input('to');
            if ($site_id === 0) {
                $site_id = null;
            }
            $datetime = $request->input('datetime');
            $bag = Bag::whereId($bag_id)->first();
            $last_site = $bag->latestMovement->toSite()->first();
            $movement = new BagMovement();
            $movement->to = $site_id;
            $movement->from = $last_site->id;
            $movement->bag_id = $bag_id;
            $movement->datetime = $datetime;
            $movement->save();

            if (is_null($movement->to)) {
                $sale = new Sale();
                $sale->price = $request->input('price');
                $sale->bag_id = $bag_id;
                $sale->datetime = $datetime;
                $sale->save();

                $bag->is_sold = true;
                $bag->save();
            }

            return ['id' => $movement->id];
        });
    } catch (Exception $e) {
        Log::error($e);
        return response()->json([
            'message' => 'There was an error'
        ], 500);
    }
});
Route::post('/bag', function (Request $request) {
    try {
        return DB::transaction(function () use ($request) {
            $name = $request->input('name');
            $price = $request->input('price');
            $date_obtained = $request->input('date_obtained');
            $initial_site = $request->input('initial_site');

            $bag = new Bag();
            $bag->name = $name;
            $bag->price = $price;
            $bag->date_obtained = $date_obtained;
            $bag->save();

            $bag_movement = new BagMovement();
            $bag_movement->bag_id = $bag->id;
            $bag_movement->to = $initial_site;
            $bag_movement->datetime = $date_obtained;
            $bag_movement->save();

            $images = $request->file('images');
            foreach ($images as $index => $image) {
                $path = $image->storeAs('img/bags', "bag-{$index}-{$bag->id}."  . $image->getClientOriginalExtension(), 'public');
                $bag_image = new BagImage();
                $bag_image->name = "{$bag->name} {$index}";
                $bag_image->file_name = $path;
                $bag_image->bag_id = $bag->id;
                $bag_image->save();
            }
        });
    } catch (Exception $e) {
        Log::error($e);
        return response()->json(['message' => 'There was an error'], 500);
    }
});
// Get all bags in a site
Route::get('/site/{id}/bags', function ($id) {
    return ['data' => Bag::whereHas('latestMovement', function ($query) use ($id) {
        return $query->where('to', $id);
    })->get()];
});
Route::post('/bag/barcodes', function (Request $request) {
    $bags = Bag::all()->whereIn('barcode', $request->input('barcodes'));
    $bag_map = [];
    foreach ($bags as $bag) {
        $bag_map[$bag->barcode] = $bag;
    }
    return ['data' => $bag_map];
});
Route::get('/sales/recent', function () {
    return ['data' => Sale::with('bag')->latest('datetime')->limit(10)->get()];
});
Route::get('/sales', function (Request $request) {
    $offset = $request->input('first', 0);
    $limit = $request->input('rows', 10);
    $query = Sale::with('bag')->with('site');
    $filters = json_decode($request->input('filters'), true) ?? [];
    if ($sites = $filters['site']['value'] ?? null) {
        foreach ($sites as $index => $site) {
            if ($index === 0) {
                $query->whereHas('site', function ($query) use ($site) {
                    return $query->where('sites.id', '=', $site['id']);
                });
            } else {
                $query->orWhereHas('site', function ($query) use ($site) {
                    return $query->where('sites.id', '=', $site['id']);
                });
            }
        }
    }
    $sort = $request->input('multiSortMeta', []);
    foreach ($sort as $s) {
        $s = json_decode($s, true);
        $field = '';
        if ($s['field'] === 'price' || $s['field'] === 'datetime') {
            $field = $s['field'];
        }
        if (!$field) continue;
        $query->orderBy($field, $s['order'] === 1 ? 'asc' : 'desc');
    }
    $data = ['count' => $query->count(), 'data' => $query->offset($offset)->limit($limit)->get()];
    return $data;
});
Route::get('/orders', function (Request $request) {
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
});
Route::post('orders', function (Request $request) {
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
});
Route::get('/sales/report', function (Request $request) {
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date') . ' 23:59:59';
    $orders = Order::whereBetween('created_at', [$start_date, $end_date])->orderBy('created_at')->get();
    $orders_report = [];

    // Set to month of first day
    $orders_report_start = new DateTime($start_date);
    $orders_report_start->modify($orders_report_start->format('Y-m-1'));
    $orders_report_end = new DateTime($end_date);
    $orders_report_end->modify($orders_report_end->format('Y-m-1'));

    if (
        $orders_report_start->format('Y') === $orders_report_end->format('Y')
    ) {
        $format = 'F';
    } else {
        $format = 'Y F';
    }

    while ($orders_report_start->format('Y-m') !== $orders_report_end->format('Y-m')) {
        $orders_report[$orders_report_start->format($format)] = 0;
        $orders_report_start->add(new DateInterval("P1M"));
    }

    foreach ($orders as $order) {
        $datetime = new DateTime($order->datetime);
        $key = $datetime->format($format);
        if (!array_key_exists($key, $orders_report)) {
            $orders_report[$key] = 0;
        }
        $orders_report[$key] += $order->total;
    }
    return ['data' => array_values($orders_report), 'labels' => array_keys($orders_report)];
});
Route::get('/resolve-products', function (Request $request) {
    $ids = explode(',', $request->get('products'));
    $quantities = explode(',', $request->get('quantities'));
    Log::info($ids);
    Log::info($quantities);
    $resources = [];
    $products = [];
    foreach ($ids as $index => $id) {
        $quantity = $quantities[$index];
        $product = Product::find($id);
        if (!$product) continue;
        $products[] = $product;
        foreach ($product->resources as $resource) {
            if (!array_key_exists($resource->id, $resources)) {
                $resources[$resource->id] = [
                    'resource' => $resource,
                    'quantity' => -$resource->stock
                ];
            }
            $resources[$resource->id]['quantity'] += (int)$quantity * $resource->pivot->quantity;
        }
    }
    foreach ($resources as $resource_id => $resource) {
        if ($resource['quantity'] < 0) $resources[$resource_id]['quantity'] = 0;
    }
    return ['products' => $products, 'resources' => $resources];
})->name('resolve-products');
Route::get('/products', function (Request $request) {
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
});
Route::get('/resources', function (Request $request) {
    $name = $request->get('name');
    $offset = $request->get('first', 0);
    $limit = $request->get('rows', 1000);
    $query = Resource::query();
    $filters = json_decode($request->get('filters'), true) ?? [];
    Log::debug($filters);
    if ($name = Arr::get($filters, 'global.value', '')) {
        Log::info($name);
        $query = $query->where('name', 'like', "%$name%");
    }

    $sort = $request->input('multiSortMeta', []);
    foreach ($sort as $s) {
        $s = json_decode($s, true);
        $field = '';
        if (in_array($s['field'], ['name', 'stock'])) {
            $field = $s['field'];
        }
        if (!$field) continue;
        $query->orderBy($field, $s['order'] === 1 ? 'asc' : 'desc');
    }
    return [
        'count' => $query->count(),
        'data' => $query->offset($offset)->limit($limit)->get(),
    ];
});
Route::post('/products', function (Request $request) {
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
});
Route::post('/resources', function (Request $request) {
    DB::beginTransaction();
    $resource = new Resource([
        'name' => $request->post('name'),
        'unit' => $request->post('unit'),
        'description' => $request->post('description'),
        'stock' => $request->post('stock')
    ]);
    $resource->save();
    $images = $request->file('images');
    foreach ($images ?? [] as $index => $image) {
        $path = $image->storeAs('img/products', "product-{$index}-{$resource->id}."  . $image->getClientOriginalExtension(), 'public');
        $resource->images()->create([
            'name' => "{$resource->name} {$index}",
            'file_name' => $path,
        ]);
    }
    DB::commit();
    return ['id' => $resource->id];
});
Route::post('/resources/{resource}/update-stock/{stock}', function (Request $request, Resource $resource, int $stock) {
    $resource->stock = $stock;
    $resource->save();
    return ['message' => 'Updated stock'];
});
Route::post('/products/{product}/update-stock/{stock}', function (Request $request, Product $product, int $stock) {
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
});
Route::get('/resource/{resource}/image', function (Request $request, Resource $resource) {
    if ($resource->images->count() > 0) {
        return response()->file(base_path(Resource::UPLOAD_PATH . DIRECTORY_SEPARATOR . $resource->images->first()->file_name));
    }
    return response()->file(public_path('img/placeholder-200x200.jpg'));
});
