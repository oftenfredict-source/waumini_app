<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_dependants', function (Blueprint $table) {
            $table->boolean('is_student')->default(false)->after('kipaimara_by');
            $table->string('education_level')->nullable()->after('is_student');
            $table->string('school_region')->nullable()->after('education_level');
            $table->string('school_district')->nullable()->after('school_region');
            $table->string('school_ward')->nullable()->after('school_district');
            $table->string('school_street')->nullable()->after('school_ward');
        });
    }

    public function down(): void
    {
        Schema::table('member_dependants', function (Blueprint $table) {
            $table->dropColumn([
                'is_student',
                'education_level',
                'school_region',
                'school_district',
                'school_ward',
                'school_street',
            ]);
        });
    }
};
