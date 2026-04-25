<?php
namespace App\Services;

use App\Models\Promotion;

class PromotionService
{
    protected Promotion $promotionModel;

    public function __construct()
    {
        $this->promotionModel = new Promotion();
    }

    public function getActiveVouchers(): array
    {
        return $this->promotionModel->getActiveVouchers();
    }

    public function validateVoucher(string $code, float $totalAmount): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập mã voucher'];
        }

        $voucher = $this->promotionModel->getVoucherByCode($code);
        if (!$voucher) {
            return ['success' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn'];
        }

        if (!empty($voucher['min_order_amount']) && $totalAmount < (float)$voucher['min_order_amount']) {
            return [
                'success' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format((float)$voucher['min_order_amount']) . 'đ'
            ];
        }

        $discount = $this->calculateDiscount($voucher, $totalAmount);
        return [
            'success' => true,
            'voucher' => $voucher,
            'discount' => $discount,
            'final_total' => $this->calculateFinalPrice($totalAmount, $discount),
            'message' => 'Áp dụng voucher thành công'
        ];
    }

    public function calculateDiscount(array $voucher, float $totalAmount): float
    {
        $discount = 0;

        if (!empty($voucher['discount_percent']) && (float)$voucher['discount_percent'] > 0) {
            $discount = $totalAmount * ((float)$voucher['discount_percent'] / 100);
            if (!empty($voucher['max_discount']) && (float)$voucher['max_discount'] > 0) {
                $discount = min($discount, (float)$voucher['max_discount']);
            }
        } elseif (!empty($voucher['discount_amount']) && (float)$voucher['discount_amount'] > 0) {
            $discount = (float)$voucher['discount_amount'];
        }

        return max(0, min($discount, $totalAmount));
    }

    public function calculateFinalPrice(float $totalAmount, float $discount): float
    {
        return max(0, $totalAmount - max(0, $discount));
    }
}
