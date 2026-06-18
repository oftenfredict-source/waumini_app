<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_services', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('service_type', 30);
            $table->string('title')->nullable();
            $table->date('service_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('theme')->nullable();
            $table->string('preacher')->nullable();
            $table->string('venue')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'service_date']);
            $table->index(['church_id', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_services');
    }
};
