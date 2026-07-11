<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('family_member_id')
                ->nullable()
                ->after('spouse_envelope_number')
                ->constrained('members')
                ->nullOnDelete();
            $table->string('guardian_full_name')->nullable()->after('family_member_id');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_full_name');
            $table->string('guardian_relationship', 50)->nullable()->after('guardian_phone');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_member_id');
            $table->dropColumn(['guardian_full_name', 'guardian_phone', 'guardian_relationship']);
        });
    }
};
