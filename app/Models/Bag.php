<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Bag
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property float $price
 * @property string $date_obtained
 * @property int $is_sold
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BagImage[] $images
 * @property-read int|null $images_count
 * @method static \Database\Factories\BagFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereDateObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereIsSold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bag whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BagMovement[] $movement
 * @property-read int|null $movement_count
 */
class Bag extends Model
{
    use HasFactory;

    public function images()
    {
        return $this->hasMany(BagImage::class);
    }

    public function movement()
    {
        return $this->hasMany(BagMovement::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }
}
