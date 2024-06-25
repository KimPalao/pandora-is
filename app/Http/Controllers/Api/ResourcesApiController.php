<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourcesApiController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->get('name');
        $offset = $request->get('first', 0);
        $limit = $request->get('rows', 1000);
        $query = Resource::query();
        $filters = json_decode($request->get('filters'), true) ?? [];
        if ($name = Arr::get($filters, 'global.value', '')) {
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
    }

    public function create(Request $request)
    {
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
            $path = $image->storeAs('img/resources', "resource-{$index}-{$resource->id}."  . $image->getClientOriginalExtension(), 'public');
            $resource->images()->create([
                'name' => "{$resource->name} {$index}",
                'file_name' => $path,
            ]);
        }
        DB::commit();
        return ['id' => $resource->id];
    }

    public function updateStock(Request $request, Resource $resource, int $stock)
    {
        $resource->stock = $stock;
        $resource->save();
        return ['message' => 'Updated stock'];
    }

    public function image(Request $request, Resource $resource)
    {
        if ($resource->images->count() > 0) {
            return response()->file(Storage::path('public' . DIRECTORY_SEPARATOR . $resource->images->first()->file_name));
        }
        return response()->file(public_path('img/placeholder-200x200.jpg'));
    }

    public function lowOnStock(Request $request)
    {
        return ['data' => Resource::query()->orderBy('stock')->limit(10)->get()];
    }
}
