<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('user_type')->default('owner')->after('password');
            $table->string('status')->default('active')->after('user_type');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'phone', 'user_type', 'status', 'last_login_at']);
        });
    }
};
