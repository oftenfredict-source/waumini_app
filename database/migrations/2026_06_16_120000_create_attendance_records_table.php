<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('member_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('dependant_id')->nullable()->constrained('member_dependants')->cascadeOnDelete();
            $table->timestamp('attended_at')->useCurrent();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'source_type', 'source_id']);
            $table->index('member_id');
            $table->index('dependant_id');
        });

        Schema::table('church_services', function (Blueprint $table) {
            $table->unsignedInteger('guests_count')->nullable()->after('notes');
        });

        Schema::table('special_events', function (Blueprint $table) {
            $table->unsignedInteger('guests_count')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('special_events', function (Blueprint $table) {
            $table->dropColumn('guests_count');
        });

        Schema::table('church_services', function (Blueprint $table) {
            $table->dropColumn('guests_count');
        });

        Schema::dropIfExists('attendance_records');
    }
};
