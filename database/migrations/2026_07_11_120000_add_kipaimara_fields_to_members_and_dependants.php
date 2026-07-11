<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('is_kipaimara')->default(false)->after('baptized_by');
            $table->date('kipaimara_date')->nullable()->after('is_kipaimara');
            $table->string('kipaimara_place')->nullable()->after('kipaimara_date');
            $table->string('kipaimara_by')->nullable()->after('kipaimara_place');
        });

        Schema::table('member_dependants', function (Blueprint $table) {
            $table->boolean('is_kipaimara')->default(false)->after('baptized_by');
            $table->date('kipaimara_date')->nullable()->after('is_kipaimara');
            $table->string('kipaimara_place')->nullable()->after('kipaimara_date');
            $table->string('kipaimara_by')->nullable()->after('kipaimara_place');
        });
    }

    public function down(): void
    {
        Schema::table('member_dependants', function (Blueprint $table) {
            $table->dropColumn(['is_kipaimara', 'kipaimara_date', 'kipaimara_place', 'kipaimara_by']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['is_kipaimara', 'kipaimara_date', 'kipaimara_place', 'kipaimara_by']);
        });
    }
};
