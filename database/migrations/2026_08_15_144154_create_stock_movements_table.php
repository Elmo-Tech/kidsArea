<?php

use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    public function up(): void
    {
        Schema::create(
            'stock_movements',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('inventory_item_id')
                    ->constrained('inventory_items')
                    ->restrictOnDelete();

                $table->tinyInteger('type');

                /*
                 * Always store a positive quantity.
                 *
                 * The movement type determines
                 * whether stock goes IN or OUT.
                 */
                $table->decimal(
                    'quantity',
                    14,
                    3
                );

                /*
                 * Snapshot of cost at movement time.
                 * Useful especially for stock purchases.
                 */
                $table->decimal(
                    'unit_cost',
                    12,
                    4
                )->nullable();

                $table->decimal(
                    'total_cost',
                    14,
                    4
                )->nullable();

                /*
                 * Optional source.
                 *
                 * Later examples:
                 * cafe_order
                 * cafe_order_item
                 * manual
                 * stock_purchase
                 */
                $table->string(
                    'source_type',
                    100
                )->nullable();

                $table->unsignedBigInteger(
                    'source_id'
                )->nullable();

                $table->dateTime(
                    'movement_at'
                );

                $table->text('notes')
                    ->nullable();

                $table->timestamps();
                $this->createdUpdatedByRelationship($table);
                $table->index([
                    'inventory_item_id',
                    'movement_at',
                ]);

                $table->index([
                    'type',
                    'movement_at',
                ]);

                $table->index([
                    'source_type',
                    'source_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'stock_movements'
        );
    }
};
