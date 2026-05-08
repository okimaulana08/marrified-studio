<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('bride_name');
            $table->string('bride_nickname')->nullable();
            $table->string('bride_parents')->nullable();
            $table->string('bride_instagram')->nullable();
            $table->string('bride_photo_path')->nullable();
            $table->string('groom_name');
            $table->string('groom_nickname')->nullable();
            $table->string('groom_parents')->nullable();
            $table->string('groom_instagram')->nullable();
            $table->string('groom_photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couples');
    }
};
