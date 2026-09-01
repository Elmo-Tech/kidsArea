<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'الإيجار',
            'الكهرباء',
            'المياه',
            'الصيانة',
            'النظافة',
            'المستلزمات',
            'مستلزمات الأنشطة',
            'مستلزمات الكافيه',
            'ألعاب ومعدات',
            'النقل والمواصلات',
            'التسويق والإعلانات',
            'خدمات الإنترنت والاتصالات',
            'رسوم واشتراكات',
            'أخرى',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
