<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_registration_applications', function (Blueprint $table) {
            $table->dropUnique(['application_number']);
            $table->unique(['church_id', 'application_number']);
        });
    }

    public function down(): void
    {
        Schema::table('member_registration_applications', function (Blueprint $table) {
            $table->dropUnique(['church_id', 'application_number']);
            $table->unique('application_number');
        });
    }
};
