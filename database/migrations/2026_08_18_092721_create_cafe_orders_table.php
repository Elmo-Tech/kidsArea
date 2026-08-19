<?php

use App\Enums\CafeOrderStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('cafe_orders', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('visit_id')
                ->nullable()
                ->constrained('visits')
                ->nullOnDelete();

            $table->string('order_number')
                ->unique();

            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->decimal('total', 12, 2)
                ->default(0);

            $table->tinyInteger('status')
                ->default(
                    CafeOrderStatusEnum::DRAFT->value
                );

            $table->dateTime('confirmed_at')
                ->nullable();

            $table->dateTime('completed_at')
                ->nullable();

            $table->dateTime('cancelled_at')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'visit_id',
                'status',
            ]);

            $table->index([
                'status',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_orders');
    }
};
