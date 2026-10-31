<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Asset
 *
 * @property int $id
 * @property int $variable_id
 * @property string $count
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Variable $variable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereVariableId($value)
 *
 * @mixin \Eloquent
 */
class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'user_id',
        'psc', 'irr', 'blue', 'red', 'green', 'satisfaction', 'effect',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }
}
