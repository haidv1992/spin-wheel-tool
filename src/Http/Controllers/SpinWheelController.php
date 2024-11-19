<?php

namespace Dbiz\SpinWheelTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Dbiz\SpinWheelTool\Models\SpinWheelSetting;
use Dbiz\SpinWheelTool\Services\PrizeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SpinWheelController extends Controller
{
	protected $prizeService;
	
	/**
	 * Constructor to inject PrizeService.
	 *
	 * @param PrizeService $prizeService
	 */
	public function __construct(PrizeService $prizeService)
	{
		$this->prizeService = $prizeService;
	}
	
	/**
	 * Display the spin wheel configuration and prizes.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$settings = SpinWheelSetting::firstOrNew([]);
		$defaultConfig = [
			'spinDuration' => 4000,
			'numberOfRevolutions' => 20,
			'easingFunction' => 'cubicOut',
			'rotationResistance' => -35,
		];
		
		if (!$settings->exists) {
			$settings->prizes = Config::get('spinwheeltool.prizes', []);
			$settings->config = Config::get('spinwheeltool.config', $defaultConfig);
			$settings->save();
		}
		
		$config = array_merge($defaultConfig, $settings->config ?? []);
		
		return response()->json([
			'prizes' => collect($settings->prizes)->map(function ($prize) {
				return [
					'id' => $prize['id'],
					'name' => $prize['name'],
					'icon' => $prize['icon'] ?? null,
					'display_option' => $prize['display_option'] ?? 'both',
					'backgroundColor' => $prize['backgroundColor'] ?? "#FFFFFF",
				];
			}),
			'config' => $config,
		]);
	}
	
	public function store(Request $request)
	{
		$validated = $request->validate([
			'prizes' => 'required|array',
			'prizes.*.id' => 'required|numeric',
			'prizes.*.name' => 'required|string',
			'prizes.*.backgroundColor' => 'required|string',
			'prizes.*.weight' => 'required|numeric|min:0',
			'prizes.*.display_option' => 'required|string|in:icon,text,both',
			'icons.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
			'config' => 'nullable|array',
		]);
		
		$prizes = $validated['prizes'];
		$config = $validated['config'] ?? [];
		// Lấy cấu hình hiện tại để giữ lại icon cũ nếu cần
		$settings = SpinWheelSetting::first();
		$existingPrizes = $settings->prizes ?? [];
		
		// Xử lý upload icon cho mỗi prize
		foreach ($prizes as &$prize) {
			$prizeId = $prize['id'];
			
			if ($request->hasFile("icons.$prizeId")) {
				$file = $request->file("icons.$prizeId");
				$path = $file->store('public/prize_icons');
				$prize['icon'] = Storage::url($path);
			} else {
				// Giữ nguyên icon cũ nếu không upload icon mới
				foreach ($existingPrizes as $existingPrize) {
					if ($existingPrize['id'] == $prizeId && isset($existingPrize['icon'])) {
						$prize['icon'] = $existingPrize['icon'];
						break;
					}
				}
			}
		}
		
		SpinWheelSetting::updateOrCreate([], [
			'prizes' => $prizes,
			'config' => $config,
		]);
		
		return response()->json(['message' => 'Settings saved successfully!']);
	}
	
	/**
	 * Check if the user can spin based on spin limits.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function checkSpin(Request $request)
	{
		$spinLimit = Config::get('spinwheeltool.spin_limit', 3);
		
		// Here, implement minimal logic or logging since external models are removed
		// You can log the checkSpin action and return a default response
		Log::info('checkSpin called.', [
			'ip_address' => $request->ip(),
			'user_agent' => $request->header('User-Agent'),
			'spin_limit' => $spinLimit,
		]);
		
		// Default response allowing spin
		return response()->json([
			'can_spin' => true,
		]);
	}
	
	/**
	 * Handle the spin action.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function spin(Request $request)
	{
		// Fetch the prize based on weighted probability
		$prize = $this->prizeService->determinePrize();
		
		if (!$prize) {
			return response()->json([
				'message' => 'No prizes available.',
			], 400);
		}
		
		// For simplicity, just return the prize without any tracking
		return response()->json([
			'prize_id'   => $prize['id'],
			'prize_name' => $prize['name'],
		]);
	}
	
	/**
	 * Handle the submission of customer information after spinning.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function submitCustomerInfo(Request $request)
	{
		// Since external models are removed, perform minimal action
		// Log the submission attempt
		
		$validated = $request->validate([
			'name'       => 'required|string|max:255',
			'phone'      => 'required|string|regex:/^\d{10,15}$/',
			'email'      => 'required|email|max:255',
			'spin_token' => 'required|uuid',
		]);
		
		Log::info('submitCustomerInfo called.', [
			'name'       => $validated['name'],
			'phone'      => $validated['phone'],
			'email'      => $validated['email'],
			'spin_token' => $validated['spin_token'],
		]);
		
		// Optionally, respond with a success message
		return response()->json([
			'message' => 'Customer information received. Please override this method to implement custom logic.',
		]);
	}
}
