<?php

namespace Database\Seeders;

use App\Models\LuckyWheelPrize;
use Illuminate\Database\Seeder;

class LuckyWheelPrizeSeeder extends Seeder
{
    public function run(): void
    {
        $prizes = [
            [
                'title' => 'Free Soft Drink',
                'description' => 'Enjoy 1 complimentary chilled soft drink or iced tea with your meal.',
                'prize_type' => 'free_product',
                'discount_value' => null,
                'coupon_code' => 'FREEDRINK',
                'probability' => 25,
                'slice_color' => '#F59E0B',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'title' => '10% Off Bill',
                'description' => 'Get 10% discount on your entire dining order.',
                'prize_type' => 'discount_percentage',
                'discount_value' => 10.00,
                'coupon_code' => 'SPIN10OFF',
                'probability' => 20,
                'slice_color' => '#8B5CF6',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'title' => '$2.00 Off Coupon',
                'description' => 'Direct $2.00 discount on orders above $10.',
                'prize_type' => 'discount_fixed',
                'discount_value' => 2.00,
                'coupon_code' => 'SAVE2USD',
                'probability' => 20,
                'slice_color' => '#10B981',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'title' => 'Free Dessert',
                'description' => 'Complimentary scoop of ice cream or daily special dessert.',
                'prize_type' => 'free_product',
                'discount_value' => null,
                'coupon_code' => 'SWEETDESSERT',
                'probability' => 15,
                'slice_color' => '#EC4899',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'title' => '15% Off VIP Coupon',
                'description' => 'Special 15% discount for your next dining adventure.',
                'prize_type' => 'discount_percentage',
                'discount_value' => 15.00,
                'coupon_code' => 'VIP15OFF',
                'probability' => 10,
                'slice_color' => '#3B82F6',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
            [
                'title' => 'Better Luck Next Time',
                'description' => 'Thank you for playing! Try again on your next order.',
                'prize_type' => 'try_again',
                'discount_value' => null,
                'coupon_code' => null,
                'probability' => 10,
                'slice_color' => '#6B7280',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ],
        ];

        foreach ($prizes as $prize) {
            LuckyWheelPrize::create($prize);
        }
    }
}
