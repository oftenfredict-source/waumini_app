<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
            $table->text('archive_reason')->nullable()->after('archived_at');
            $table->foreignId('archived_by')->nullable()->after('archive_reason')->constrained('users')->nullOnDelete();

            $table->index(['church_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropIndex(['church_id', 'archived_at']);
            $table->dropColumn(['archived_at', 'archive_reason', 'archived_by']);
        });
    }
};
