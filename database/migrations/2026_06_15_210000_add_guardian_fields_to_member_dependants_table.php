<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_dependants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->foreignId('member_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('guardian_full_name')->nullable()->after('member_id');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_full_name');
            $table->string('guardian_relationship', 50)->nullable()->after('guardian_phone');
        });
    }

    public function down(): void
    {
        Schema::table('member_dependants', function (Blueprint $table) {
            $table->dropColumn(['guardian_full_name', 'guardian_phone', 'guardian_relationship']);
            $table->dropConstrainedForeignId('member_id');
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
        });
    }
};
