<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('budget_name');
            $table->string('budget_type', 30)->default('annual');
            $table->string('purpose')->nullable();
            $table->string('primary_offering_type', 50)->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->unsignedSmallInteger('fiscal_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_budget', 14, 2);
            $table->decimal('allocated_amount', 14, 2)->default(0);
            $table->decimal('spent_amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('approval_status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'fiscal_year']);
            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'approval_status']);
        });

        Schema::create('budget_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('amount', 14, 2);
            $table->string('responsible_person');
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('budget_id');
        });

        Schema::create('budget_offering_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('offering_type', 50);
            $table->decimal('allocated_amount', 14, 2);
            $table->decimal('used_amount', 14, 2)->default(0);
            $table->decimal('available_amount', 14, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['budget_id', 'offering_type']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained()->nullOnDelete();
            $table->string('expense_category', 50);
            $table->string('expense_name');
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('payment_method', 30)->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('vendor')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'expense_date']);
            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'approval_status']);
            $table->index('budget_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budget_offering_allocations');
        Schema::dropIfExists('budget_line_items');
        Schema::dropIfExists('budgets');
    }
};
