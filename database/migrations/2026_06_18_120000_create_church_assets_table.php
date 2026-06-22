<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('church_branches')->nullOnDelete();
            $table->string('asset_tag', 30);
            $table->string('name');
            $table->string('category', 50);
            $table->text('description')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_value', 14, 2)->nullable();
            $table->decimal('current_value', 14, 2)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('condition', 30)->default('good');
            $table->string('status', 30)->default('active');
            $table->foreignId('custodian_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('disposed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['church_id', 'asset_tag']);
            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_assets');
    }
};
