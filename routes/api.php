<?php

use App\Models\Bag;
use App\Models\BagImage;
use App\Models\BagMovement;
use App\Models\Sale;
use App\Models\Site;
use Illuminate\Http\Request;
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
Route::get('/sales/report', function (Request $request) {
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date') . ' 23:59:59';
    $sales = Sale::whereBetween('datetime', [$start_date, $end_date])->orderBy('datetime')->get();
    $sales_report = [];

    // Set to month of first day
    $sales_report_start = new DateTime($start_date);
    $sales_report_start->modify($sales_report_start->format('Y-m-1'));
    $sales_report_end = new DateTime($end_date);
    $sales_report_end->modify($sales_report_end->format('Y-m-1'));

    if (
        $sales_report_start->format('Y') === $sales_report_end->format('Y')
    ) {
        $format = 'F';
    } else {
        $format = 'Y F';
    }

    while ($sales_report_start->format('Y-m') !== $sales_report_end->format('Y-m')) {
        $sales_report[$sales_report_start->format($format)] = 0;
        $sales_report_start->add(new DateInterval("P1M"));
    }

    foreach ($sales as $sale) {
        $datetime = new DateTime($sale->datetime);
        $key = $datetime->format($format);
        if (!array_key_exists($key, $sales_report)) {
            $sales_report[$key] = 0;
        }
        $sales_report[$key] += $sale->price;
    }
    return ['data' => array_values($sales_report), 'labels' => array_keys($sales_report)];
});
