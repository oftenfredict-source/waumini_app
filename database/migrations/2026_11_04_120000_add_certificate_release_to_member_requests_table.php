<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_requests', function (Blueprint $table) {
            $table->timestamp('certificate_released_at')->nullable()->after('certificate_generated_at');
            $table->foreignId('certificate_released_by')->nullable()->after('certificate_released_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('member_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_released_by');
            $table->dropColumn('certificate_released_at');
        });
    }
};
