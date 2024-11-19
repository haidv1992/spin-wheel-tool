<?php

namespace Dbiz\SpinWheelTool\Services;


use Dbiz\SpinWheelTool\Models\SpinWheelSetting;

class PrizeService
{
    protected $prizes;

    public function __construct()
    {
        $settings = SpinWheelSetting::first();
        $this->prizes = $settings->prizes ?? [];
    }
	
	/**
	 * vi
	 * Vòng lặp foreach duyệt qua từng giải thưởng trong $prizes.
	 * Với mỗi giải thưởng, nếu $random nhỏ hơn hoặc bằng trọng số (weight) của giải thưởng đó, giải thưởng đó sẽ được chọn và trả về ngay lập tức.
	 * Nếu không, $random sẽ trừ đi giá trị trọng số của giải thưởng hiện tại ($random -= $prize['weight'];) và tiếp tục vòng lặp.
	 * Cách này giúp giải thưởng có trọng số lớn hơn sẽ chiếm nhiều khả năng được chọn hơn. Giả sử bạn có ba giải thưởng:
	 *
	 * Giải thưởng 1 có trọng số là 5
	 * Giải thưởng 2 có trọng số là 3
	 * Giải thưởng 3 có trọng số là 2
	 * Khi sinh ra số ngẫu nhiên từ 1 đến 10 (tổng các trọng số), nếu số đó nằm trong khoảng 1-5, Giải thưởng 1 sẽ trúng, nếu nằm trong khoảng 6-8, Giải thưởng 2 sẽ trúng, và nếu nằm trong khoảng 9-10, Giải thưởng 3 sẽ trúng.
     *
     * @return array|null
     */
	public function determinePrize()
	{
		if (empty($this->prizes)) {
			return null;
		}
		
		// Lọc các giải thưởng hợp lệ (trọng số > 0)
		$validPrizes = array_filter($this->prizes, function ($prize) {
			return isset($prize['weight']) && $prize['weight'] > 0;
		});
		
		if (empty($validPrizes)) {
			return null;
		}
		
		// Tính tổng trọng số
		$totalWeight = array_sum(array_column($validPrizes, 'weight'));
		
		if ($totalWeight <= 0) {
			return null;
		}
		
		// Sinh số ngẫu nhiên
		$randomThreshold = random_int(1, $totalWeight);
		
		// Xác định giải thưởng
		foreach ($validPrizes as $prize) {
			if ($randomThreshold <= $prize['weight']) {
				return $prize;
			}
			$randomThreshold -= $prize['weight'];
		}
		
		// Fallback
		return null;
	}
	
}
