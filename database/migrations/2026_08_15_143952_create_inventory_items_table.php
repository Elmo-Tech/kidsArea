<?php

use App\Enums\InventoryUnitEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();

            $table->string('name');

            $table->tinyInteger('base_unit');

            /*
             * Cached stock balance.
             * Stock movements will be the historical source.
             */
            $table->decimal(
                'current_quantity',
                14,
                3
            )->default(0);

            /*
             * Used later for low-stock alerts.
             */
            $table->decimal(
                'minimum_quantity',
                14,
                3
            )->nullable();

            /*
             * Average or last unit cost.
             * We'll decide the costing method later.
             */
            $table->decimal(
                'unit_cost',
                12,
                4
            )->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->text('notes')
                ->nullable();

            $table->timestamps();
            $this->createdUpdatedByRelationship($table);
            $table->index('is_active');
            $table->index('base_unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
