<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 50);
            $table->string('category_other')->nullable();
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('speaker')->nullable();
            $table->string('venue')->nullable();
            $table->decimal('budget_amount', 14, 2)->nullable();
            $table->unsignedInteger('expected_attendance')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'event_date']);
            $table->index(['church_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_events');
    }
};
