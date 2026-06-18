<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offerings', function (Blueprint $table) {
            $table->foreignId('church_service_id')
                ->nullable()
                ->after('member_id')
                ->constrained('church_services')
                ->nullOnDelete();

            $table->index(['church_id', 'church_service_id']);
        });
    }

    public function down(): void
    {
        Schema::table('offerings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('church_service_id');
        });
    }
};
