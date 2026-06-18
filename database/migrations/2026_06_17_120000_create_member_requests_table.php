<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_leader_id')->nullable()->constrained('leaders')->nullOnDelete();
            $table->string('reference_number', 30);
            $table->string('type', 50);
            $table->string('subject');
            $table->text('description');
            $table->string('status', 30)->default('pending');
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['church_id', 'reference_number']);
            $table->index(['church_id', 'status']);
            $table->index(['assigned_leader_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_requests');
    }
};
