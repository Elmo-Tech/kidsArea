<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Traits\CreatedUpdatedByMigration;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_payrolls', function (Blueprint $table): void {
            $table->decimal('paid_amount', 12, 2)
                ->default(0);

            $table->unsignedTinyInteger('payment_status')
                ->default(0);

            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payrolls', function (Blueprint $table): void {
            $table->dropIndex([
                'payment_status',
            ]);

            $table->dropColumn([
                'paid_amount',
                'payment_status',
            ]);
        });
    }
};
