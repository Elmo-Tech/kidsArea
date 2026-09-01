<?php

use App\Enums\CashRegisterStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 150);

            $table->tinyInteger('status')
                ->default(CashRegisterStatusEnum::ACTIVE->value);

            $table->text('notes')->nullable();

            $table->boolean('is_main')->default(false);

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
