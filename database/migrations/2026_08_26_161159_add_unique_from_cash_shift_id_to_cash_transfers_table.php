<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transfers', function (Blueprint $table): void {
            $table->unique(
                'from_cash_shift_id',
                'cash_transfers_from_shift_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_transfers', function (Blueprint $table): void {
            $table->dropUnique(
                'cash_transfers_from_shift_unique'
            );
        });
    }
};
