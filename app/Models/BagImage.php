<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BagImage
 *
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property int $bag_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\BagImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereBagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BagImage extends Model
{
    use HasFactory;
}
