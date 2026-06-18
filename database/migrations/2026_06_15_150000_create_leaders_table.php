<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('position', 50);
            $table->string('position_title')->nullable();
            $table->text('description')->nullable();
            $table->date('appointment_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('appointed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_active']);
            $table->index(['church_id', 'position']);
            $table->index(['member_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaders');
    }
};
