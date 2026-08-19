<?php

use App\Enums\VisitCheckoutStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('visit_checkouts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('visit_id')
                ->constrained('visits')
                ->restrictOnDelete();

            $table->decimal('activity_total', 12, 2)->default(0);
            $table->decimal('cafe_total', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->tinyInteger('status')
                ->default(VisitCheckoutStatusEnum::DRAFT->value);

            $table->dateTime('finalized_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique('visit_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_checkouts');
    }
};
