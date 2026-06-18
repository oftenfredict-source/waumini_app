<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient', 30);
            $table->string('context', 50)->default('general');
            $table->text('message');
            $table->string('status', 20);
            $table->text('provider_response')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
