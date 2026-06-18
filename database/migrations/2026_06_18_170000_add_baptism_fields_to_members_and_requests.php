<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('is_baptized')->default(false)->after('nida_number');
            $table->date('baptism_date')->nullable()->after('is_baptized');
            $table->string('baptism_place')->nullable()->after('baptism_date');
            $table->string('baptized_by')->nullable()->after('baptism_place');
        });

        Schema::table('member_dependants', function (Blueprint $table) {
            $table->boolean('is_baptized')->default(false)->after('date_of_birth');
            $table->date('baptism_date')->nullable()->after('is_baptized');
            $table->string('baptism_place')->nullable()->after('baptism_date');
            $table->string('baptized_by')->nullable()->after('baptism_place');
        });

        Schema::table('member_requests', function (Blueprint $table) {
            $table->json('request_meta')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('member_requests', function (Blueprint $table) {
            $table->dropColumn('request_meta');
        });

        Schema::table('member_dependants', function (Blueprint $table) {
            $table->dropColumn(['is_baptized', 'baptism_date', 'baptism_place', 'baptized_by']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['is_baptized', 'baptism_date', 'baptism_place', 'baptized_by']);
        });
    }
};
