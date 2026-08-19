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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();

            $table->morphs('invoiceable');

            $table->string('invoice_number')->unique();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->dateTime('issued_at');

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'invoiceable_type',
                'invoiceable_id',
            ]);

            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
