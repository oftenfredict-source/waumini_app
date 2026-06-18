<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('wedding_type', 30)->nullable()->after('marital_status');
            $table->date('wedding_date')->nullable()->after('wedding_type');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['wedding_type', 'wedding_date']);
        });
    }
};
