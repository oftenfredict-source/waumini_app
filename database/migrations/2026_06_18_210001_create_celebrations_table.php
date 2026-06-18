<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('celebrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('celebration_type', 40);
            $table->string('source', 20)->default('manual');
            $table->string('title');
            $table->date('celebration_date');
            $table->date('original_date')->nullable();
            $table->string('wedding_type', 30)->nullable();
            $table->string('status', 20)->default('upcoming');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['church_id', 'celebration_date']);
            $table->index(['church_id', 'celebration_type', 'status']);
            $table->unique(['church_id', 'member_id', 'celebration_type', 'source'], 'celebrations_member_type_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('celebrations');
    }
};
