<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24);
            $table->string('name');
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('venue_name');
            $table->text('venue_address')->nullable();
            $table->string('maps_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['invitation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
