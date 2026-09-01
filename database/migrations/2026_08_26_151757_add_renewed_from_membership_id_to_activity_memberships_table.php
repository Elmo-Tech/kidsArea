<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_memberships', function (Blueprint $table): void {
            $table->foreignId('renewed_from_membership_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('activity_memberships')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_memberships', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('renewed_from_membership_id');
        });
    }
};
