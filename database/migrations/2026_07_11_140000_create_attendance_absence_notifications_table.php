<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_absence_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('through_service_id')->constrained('church_services')->cascadeOnDelete();
            $table->unsignedTinyInteger('miss_count')->default(3);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['member_id', 'through_service_id'], 'absence_notify_member_service_unique');
            $table->index(['church_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_absence_notifications');
    }
};
