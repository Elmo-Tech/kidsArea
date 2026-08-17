<?php

use App\Enums\ActivityAttendanceStatusEnum;
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
            'activity_attendances',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('activity_session_id')
                    ->constrained('activity_sessions')
                    ->cascadeOnDelete();

                $table->foreignId('child_id')
                    ->constrained('children')
                    ->cascadeOnDelete();

                $table->foreignId('activity_membership_id')
                    ->nullable()
                    ->constrained('activity_memberships')
                    ->nullOnDelete();

                $table->time('check_in_at')
                    ->nullable();

                $table->time('check_out_at')
                    ->nullable();

                $table->tinyInteger('status')
                    ->default(
                        ActivityAttendanceStatusEnum::PRESENT->value
                    );

                $table->text('notes')
                    ->nullable();

                $table->timestamps();

                $this->createdUpdatedByRelationship($table);

                /*
                 * One attendance record per child per session.
                 */
                $table->unique([
                    'activity_session_id',
                    'child_id',
                ]);

                $table->index([
                    'child_id',
                    'status',
                ]);

                $table->index([
                    'activity_membership_id',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_attendances'
        );
    }
};
