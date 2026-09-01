<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_session_children', function (Blueprint $table): void {
            $table->foreignId('activity_membership_id')
                ->nullable()
                ->after('child_id')
                ->constrained('activity_memberships')
                ->nullOnDelete();

            $table->boolean('assigned_manually')
                ->default(false)
                ->after('activity_membership_id');
        });
    }

    public function down(): void
    {
        Schema::table('activity_session_children', function (Blueprint $table): void {
            $table->dropForeign(['activity_membership_id']);
            $table->dropColumn([
                'activity_membership_id',
                'assigned_manually',
            ]);
        });
    }
};
