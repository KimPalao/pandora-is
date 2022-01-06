<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Resource
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $unit
 * @property int $stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ResourceImage[] $images
 * @property-read int|null $images_count
 * @method static \Database\Factories\ResourceFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resource query()
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resource whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Resource extends Model
{
    use HasFactory;

    public $fillable = ['name', 'description', 'unit', 'stock'];

    public const UPLOAD_PATH = 'storage/app/public/img/resources';

    public function images()
    {
        return $this->hasMany(ResourceImage::class);
    }
}
