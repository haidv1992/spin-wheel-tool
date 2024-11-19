<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Spin Wheel Prizes
	|--------------------------------------------------------------------------
	|
	| Define the default prizes for the spin wheel. Each prize should have an
	| id, name, weight, display_option, and backgroundColor.
	|
	*/
	'prizes' => [
		['id' => 1, 'name' => 'Voucher for 1 year of Dbiz dining', 'weight' => 0, 'display_option' => 'both', 'backgroundColor' => '#FFC20E'],
		['id' => 2, 'name' => 'Voucher A4T1 (20%)', 'weight' => 15, 'display_option' => 'both', 'backgroundColor' => '#4AC6FF'],
		['id' => 3, 'name' => 'Voucher 50k off on 200k bill', 'weight' => 5, 'display_option' => 'both', 'backgroundColor' => '#DE2B2E'],
		['id' => 4, 'name' => 'Voucher 100k off on 500k bill', 'weight' => 5, 'display_option' => 'both', 'backgroundColor' => '#FFFFFF'],
		['id' => 5, 'name' => 'Buffetline Voucher', 'weight' => 40, 'display_option' => 'both', 'backgroundColor' => '#FFC20E'],
		['id' => 6, 'name' => 'Buffet Coca Voucher', 'weight' => 35, 'display_option' => 'both', 'backgroundColor' => '#4AC6FF'],
	],
	
	/*
	|--------------------------------------------------------------------------
	| Spin Wheel Configuration
	|--------------------------------------------------------------------------
	|
	| Define the default configuration for the spin wheel, such as spin duration,
	| number of revolutions, easing function, and rotation resistance.
	|
	*/
	'config' => [
		'spinDuration' => 4000, // in milliseconds
		'numberOfRevolutions' => 20,
		'easingFunction' => 'cubicOut',
		'rotationResistance' => -35,
	],
	
	/*
	|--------------------------------------------------------------------------
	| Spin Limit Configuration
	|--------------------------------------------------------------------------
	|
	| Define the maximum number of spins allowed per user per day.
	|
	*/
	'spin_limit' => 3,
	
	/*
	|--------------------------------------------------------------------------
	| Custom Spin Wheel Controller
	|--------------------------------------------------------------------------
	|
	| Specify a custom controller to override the default SpinWheelController.
	| If not set, the package's default controller will be used.
	|
	| Example:
	| 'controller' => App\Http\Controllers\CustomSpinWheelController::class,
	|
	*/
	'controller' => null,

];
