<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promise_guests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('guest_type', 20)->default('promised');
            $table->string('name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->date('promised_date');
            $table->foreignId('church_service_id')->nullable()->constrained('church_services')->nullOnDelete();
            $table->foreignId('special_event_id')->nullable()->constrained('special_events')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['church_id', 'promised_date']);
            $table->index(['church_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promise_guests');
    }
};
