<?php

use App\Enums\StatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();

            $table->text('description')->nullable();

            $table->tinyInteger('status')
                ->default(StatusEnum::ACTIVE->value);

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
