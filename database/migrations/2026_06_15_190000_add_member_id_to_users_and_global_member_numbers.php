<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('church_id')->constrained()->nullOnDelete();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['church_id', 'member_number']);
            $table->unique('member_number');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['member_number']);
            $table->unique(['church_id', 'member_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
        });
    }
};
