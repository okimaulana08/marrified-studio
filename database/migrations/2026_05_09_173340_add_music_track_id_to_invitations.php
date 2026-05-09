<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Couple's chosen background music. Optional — invitation works
            // fine without one. nullOnDelete so admin can remove a track
            // (e.g. licensing change) without breaking invitations using it.
            $table->foreignId('music_track_id')->nullable()->after('theme_slug')
                ->constrained('music_tracks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('music_track_id');
        });
    }
};
