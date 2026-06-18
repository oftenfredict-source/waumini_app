<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedSmallInteger('temporary_duration_value')->nullable()->after('membership_type');
            $table->string('temporary_duration_unit', 10)->nullable()->after('temporary_duration_value');
            $table->date('membership_expires_at')->nullable()->after('membership_date');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'temporary_duration_value',
                'temporary_duration_unit',
                'membership_expires_at',
            ]);
        });
    }
};
