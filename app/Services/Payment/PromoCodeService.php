<?php

namespace App\Services\Payment;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    /**
     * Find promo code by string code (case-insensitive).
     */
    public function findByCode(string $code): ?PromoCode
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return PromoCode::where('code', $normalized)->first();
    }

    /**
     * Validate a promo code against a course and fee amount.
     *
     * @return array{
     *     valid: bool,
     *     message: string,
     *     promo_code: ?PromoCode,
     *     code: string,
     *     discount_type: ?string,
     *     discount_value: float,
     *     original_amount: float,
     *     discount_amount: float,
     *     final_amount: float,
     *     is_free: bool,
     *     formatted_discount: string,
     * }
     */
    public function validateCode(string $code, Course $course, float $amount, ?User $user = null, ?string $email = null): array
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return [
                'valid' => false,
                'message' => 'Please provide a promo code.',
                'promo_code' => null,
                'code' => '',
                'discount_type' => null,
                'discount_value' => 0.00,
                'original_amount' => $amount,
                'discount_amount' => 0.00,
                'final_amount' => $amount,
                'is_free' => false,
                'formatted_discount' => '',
            ];
        }

        $promo = $this->findByCode($normalized);

        if (! $promo) {
            return [
                'valid' => false,
                'message' => 'The promo code entered does not exist or has expired.',
                'promo_code' => null,
                'code' => $normalized,
                'discount_type' => null,
                'discount_value' => 0.00,
                'original_amount' => $amount,
                'discount_amount' => 0.00,
                'final_amount' => $amount,
                'is_free' => false,
                'formatted_discount' => '',
            ];
        }

        $validity = $promo->isValidFor($course, $amount, $user, $email);

        if (! $validity['valid']) {
            return [
                'valid' => false,
                'message' => $validity['message'] ?? 'Promo code is not eligible.',
                'promo_code' => $promo,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => (float) $promo->discount_value,
                'original_amount' => $amount,
                'discount_amount' => 0.00,
                'final_amount' => $amount,
                'is_free' => false,
                'formatted_discount' => $promo->formattedDiscount(),
            ];
        }

        $discount = $promo->calculateDiscount($amount);
        $final = $promo->calculateFinalAmount($amount);

        return [
            'valid' => true,
            'message' => "Promo code '{$promo->code}' applied successfully! (" . $promo->formattedDiscount() . ')',
            'promo_code' => $promo,
            'code' => $promo->code,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
            'original_amount' => round($amount, 2),
            'discount_amount' => round($discount, 2),
            'final_amount' => round($final, 2),
            'is_free' => $final <= 0.00,
            'formatted_discount' => $promo->formattedDiscount(),
        ];
    }

    /**
     * Record promo code redemption and increment uses count.
     * Idempotent by payment_id.
     */
    public function recordUsage(
        PromoCode $promoCode,
        Course $course,
        Payment $payment,
        float $originalAmount,
        float $discountAmount,
        float $finalAmount,
        ?User $user = null,
        ?string $email = null
    ): PromoCodeUsage {
        return DB::transaction(function () use (
            $promoCode,
            $course,
            $payment,
            $originalAmount,
            $discountAmount,
            $finalAmount,
            $user,
            $email
        ) {
            $existing = PromoCodeUsage::where('payment_id', $payment->id)->first();
            if ($existing) {
                return $existing;
            }

            $userEmail = strtolower(trim((string) ($user?->email ?: $email ?: $payment->metadata['guest_data']['email'] ?? '')));

            $usage = PromoCodeUsage::create([
                'promo_code_id' => $promoCode->id,
                'course_id' => $course->id,
                'user_id' => $user?->id ?? $payment->user_id,
                'payment_id' => $payment->id,
                'user_email' => $userEmail,
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
            ]);

            $promoCode->increment('uses_count');

            return $usage;
        });
    }
}
