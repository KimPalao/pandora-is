<?php

use App\Models\Bag;
use App\Models\BagImage;
use App\Models\BagMovement;
use App\Models\Site;
use Illuminate\Http\Request;
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
    return ['data' => Bag::with('latestMovement')->get()->all()];
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
Route::put('/bag/{bag_id}/movement', function (Request $request, $bag_id) {
    $site_id = $request->input('to');
    $datetime = $request->input('datetime');
    $bag = Bag::whereId($bag_id)->first();
    $last_site = $bag->latestMovement->toSite()->first();
    $movement = new BagMovement();
    $movement->to = $site_id;
    $movement->from = $last_site->id;
    $movement->bag_id = $bag_id;
    $movement->datetime = $datetime;
    if ($movement->save()) {
        return ['id' => $movement->id];
    }
});
