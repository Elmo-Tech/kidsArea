<?php

use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_payments', function (Blueprint $table): void {
            $table->tinyInteger('payment_method')
                ->default(PaymentMethodEnum::CASH->value)
                ->after('amount');

            $table->foreignId('cash_shift_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('cash_shifts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_payments', function (Blueprint $table): void {
            $table->dropForeign(['cash_shift_id']);

            $table->dropColumn([
                'payment_method',
                'cash_shift_id',
            ]);
        });
    }
};
