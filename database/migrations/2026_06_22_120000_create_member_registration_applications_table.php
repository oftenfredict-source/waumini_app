<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_registration_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('church_branches')->nullOnDelete();
            $table->string('application_number', 40)->unique();
            $table->string('full_name');
            $table->string('phone_number', 30)->nullable();
            $table->json('registration_data');
            $table->json('dependants_data')->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('assigned_envelope_number', 3)->nullable();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_registration_applications');
    }
};
