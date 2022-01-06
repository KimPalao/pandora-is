<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ResourceImage
 *
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property int $resource_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\ResourceImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereResourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourceImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResourceImage extends Model
{
    use HasFactory;

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
