<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('variant', 32)->default('default');
            $table->integer('sort_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->json('content')->nullable();
            $table->json('decoration_overrides')->nullable();
            $table->timestamps();
            $table->index(['invitation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
