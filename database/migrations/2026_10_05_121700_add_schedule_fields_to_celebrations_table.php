<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('celebrations', function (Blueprint $table) {
            $table->string('venue')->nullable()->after('notes');
            $table->time('start_time')->nullable()->after('venue');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('special_guest')->nullable()->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('celebrations', function (Blueprint $table) {
            $table->dropColumn(['venue', 'start_time', 'end_time', 'special_guest']);
        });
    }
};
