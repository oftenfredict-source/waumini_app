<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('member_number', 30);
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone_number', 30);
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('membership_type')->default('permanent');
            $table->string('marital_status', 30)->nullable();
            $table->string('occupation')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->date('membership_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['church_id', 'member_number']);
            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
