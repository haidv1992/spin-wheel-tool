<?php

namespace Dbiz\SpinWheelTool\Components;

use Dbiz\SpinWheelTool\Models\SpinWheelSetting;
use Illuminate\View\Component;

// Import mô hình SpinWheelSetting

class SpinWheelComponent extends Component
{
	public $items;
	public $config;
	
	public function __construct()
	{
		// Tải dữ liệu items và config từ database hoặc cấu hình
		$settings = SpinWheelSetting::firstOrNew([]);
		
		$defaultConfig = [
			'spinDuration' => 4000,
			'numberOfRevolutions' => 20,
			'easingFunction' => 'cubicOut',
			'rotationResistance' => -35,
		];
		
		if (!$settings->exists) {
			$settings->prizes = [
				['id' => 1, 'name' => 'Voucher ăn Kichi 1 năm', 'weight' => 0, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#FFC20E'], // vàng
				['id' => 2, 'name' => 'Voucher A4T1 (20%)', 'weight' => 15, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#4AC6FF'], // xanh
				['id' => 3, 'name' => 'Voucher 50k bill 200k', 'weight' => 5, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#DE2B2E'], // đỏ
				['id' => 4, 'name' => 'Voucher 100k bill 500k', 'weight' => 5, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#FFFFFF'], // trắng
				['id' => 5, 'name' => 'Voucher buffetline', 'weight' => 40, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#FFC20E'], // vàng
				['id' => 6, 'name' => 'Voucher buffet Coca', 'weight' => 35, 'icon' => null, 'display_option' => 'both', 'backgroundColor' => '#4AC6FF'], // xanh
			];
			$settings->config = $defaultConfig;
			$settings->save();
		}
		
		$config = array_merge($defaultConfig, $settings->config ?? []);
		
		// Chuẩn bị dữ liệu items và config cho component
		$this->items = collect($settings->prizes)->map(function ($prize) {
			return [
				'id' => $prize['id'],
				'name' => $prize['name'],
				'icon' => $prize['icon'] ?? null,
				'display_option' => $prize['display_option'] ?? 'both',
				'backgroundColor' => $prize['backgroundColor'] ?? "#FFFFFF",
			];
		})->toArray();
		
		$this->config = $config;
	}
	
	public function render()
	{
//		return view('components.spin-wheel');
		return view('spin-wheel-tool::components.spin-wheel');
	}
}
