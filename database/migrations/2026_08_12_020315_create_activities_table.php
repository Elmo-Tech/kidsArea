<?php

use App\Enums\ActivityStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->tinyInteger('status')
                ->default(ActivityStatusEnum::ACTIVE->value);

            $table->text('notes')
                ->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
