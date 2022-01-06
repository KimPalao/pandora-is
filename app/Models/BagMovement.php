<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BagMovement
 *
 * @property int $id
 * @property int $bag_id
 * @property int|null $from
 * @property int|null $to
 * @property string $datetime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Site|null $fromSite
 * @property-read \App\Models\Site|null $toSite
 * @method static \Database\Factories\BagMovementFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereBagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BagMovement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BagMovement extends Model
{
    use HasFactory;
    public function toSite()
    {
        return $this->belongsTo(Site::class, 'to');
    }

    public function fromSite()
    {
        return $this->belongsTo(Site::class, 'from');
    }
}
