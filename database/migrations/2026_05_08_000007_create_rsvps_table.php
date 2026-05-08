<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->string('attendance', 16); // attending | not_attending | maybe
            $table->integer('party_size')->default(1);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['invitation_id', 'attendance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
