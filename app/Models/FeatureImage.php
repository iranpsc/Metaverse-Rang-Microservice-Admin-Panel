<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FeatureImage extends Model
{
	protected $table = 'feature_images';

	protected $casts = [
		'feature_id' => 'int',
	];

	protected $fillable = [
		'feature_id',
        'name'
	];

	public function feature()
	{
		return $this->belongsTo(Feature::class);
	}
}
