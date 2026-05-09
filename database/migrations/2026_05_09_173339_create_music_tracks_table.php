<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            // Admin who uploaded the track. Soft-link so we can preserve track
            // even if the admin user is later removed.
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 120);
            $table->string('artist', 120)->nullable();
            // Path relative to `music_assets` disk root, e.g. `tracks/01J...mp3`.
            $table->string('file_path');
            // Duration in whole seconds. Optional — nulled when getid3 isn't
            // available; UI just shows '—' instead of mm:ss.
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};
