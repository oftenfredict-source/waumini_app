<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bereavement_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('deceased_name');
            $table->foreignId('affected_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->text('family_details')->nullable();
            $table->string('related_departments')->nullable();
            $table->date('incident_date');
            $table->date('contribution_start_date');
            $table->date('contribution_end_date');
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->text('fund_usage')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'contribution_end_date']);
        });

        Schema::create('bereavement_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bereavement_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_contributed')->default(false);
            $table->decimal('amount', 14, 2)->nullable();
            $table->date('contribution_date')->nullable();
            $table->string('contribution_type', 20)->default('individual');
            $table->string('payment_method', 30)->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['bereavement_event_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bereavement_contributions');
        Schema::dropIfExists('bereavement_events');
    }
};
