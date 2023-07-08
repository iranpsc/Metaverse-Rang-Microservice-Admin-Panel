<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
	protected $table = 'options';

	protected $fillable = [
		'asset',
		'amount',
        'note',
        'code'
	];

    public function variable() {
        return $this->hasOne(Variable::class);
    }

    public function priceChangeLogs()
    {
        return $this->morphMany(VariableChangeLog::class, 'changeable');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

}
