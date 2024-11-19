<?php

namespace Dbiz\SpinWheelTool\Models;

use Illuminate\Database\Eloquent\Model;

class SpinWheelSetting extends Model
{
	protected $fillable = ['prizes','config'];
	protected $casts = [
		'prizes' => 'array',
		'config' => 'array',
	];
}
