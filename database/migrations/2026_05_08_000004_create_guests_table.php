<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relation', 32)->default('Bapak/Ibu/Saudara/i');
            $table->string('phone', 32)->nullable();
            $table->string('token', 16)->unique();
            $table->integer('opens_count')->default(0);
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();
            $table->index(['invitation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
