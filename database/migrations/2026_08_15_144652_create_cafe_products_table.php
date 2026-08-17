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
        Schema::create('cafe_products', function (Blueprint $table): void {
            $table->id();

            $table->string('name');

            $table->text('description')->nullable();

            $table->decimal('selling_price', 12, 2);

            $table->boolean('is_active')
                ->default(true);

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_products');
    }
};
