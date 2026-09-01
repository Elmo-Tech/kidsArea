<?php

use App\Enums\PaymentMethodEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();

            $table->morphs('payable');

            $table->decimal('amount', 12, 2);

            $table->dateTime('paid_at');

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->tinyInteger('payment_method')
                ->default(PaymentMethodEnum::CASH->value);


            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index('paid_at');


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
