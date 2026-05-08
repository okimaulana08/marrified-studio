<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guestbook_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('message');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->index(['invitation_id', 'is_visible', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestbook_messages');
    }
};
