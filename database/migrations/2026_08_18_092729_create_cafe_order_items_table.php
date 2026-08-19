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
        Schema::create('cafe_order_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('cafe_order_id')
                ->constrained('cafe_orders')
                ->cascadeOnDelete();

            $table->foreignId('cafe_product_id')
                ->constrained('cafe_products')
                ->restrictOnDelete();

            /*
             * Snapshot of product name at order time.
             */
            $table->string('product_name');

            /*
             * Snapshot of selling price.
             */
            $table->decimal('unit_price', 12, 2);

            $table->unsignedInteger('quantity');

            $table->decimal('total', 12, 2);

            $table->text('notes')
                ->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'cafe_order_id',
                'cafe_product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_order_items');
    }
};
