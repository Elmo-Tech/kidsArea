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
            'cafe_product_ingredients',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('cafe_product_id')
                    ->constrained('cafe_products')
                    ->cascadeOnDelete();

                $table->foreignId('inventory_item_id')
                    ->constrained('inventory_items')
                    ->restrictOnDelete();

                /*
                 * Quantity in the inventory item's base unit.
                 *
                 * Examples:
                 * Milk 250 ML
                 * Coffee 18 GRAM
                 * Cup 1 PIECE
                 */
                $table->decimal(
                    'quantity',
                    14,
                    3
                );

                $table->timestamps();

                $this->createdUpdatedByRelationship($table);

                $table->unique([
                    'cafe_product_id',
                    'inventory_item_id',
                ], 'cafe_product_ingredients_unique');

                $table->index(
                    'inventory_item_id'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cafe_product_ingredients'
        );
    }
};
