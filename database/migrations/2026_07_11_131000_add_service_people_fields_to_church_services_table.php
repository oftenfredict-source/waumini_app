<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_services', function (Blueprint $table) {
            $table->string('preacher_type')->nullable()->after('preacher');
            $table->foreignId('preacher_member_id')->nullable()->after('preacher_type')
                ->constrained('members')->nullOnDelete();
            $table->string('preacher_phone')->nullable()->after('preacher_member_id');

            $table->string('coordinator_type')->nullable()->after('preacher_phone');
            $table->foreignId('coordinator_member_id')->nullable()->after('coordinator_type')
                ->constrained('members')->nullOnDelete();
            $table->string('coordinator_name')->nullable()->after('coordinator_member_id');
            $table->string('coordinator_phone')->nullable()->after('coordinator_name');
        });
    }

    public function down(): void
    {
        Schema::table('church_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preacher_member_id');
            $table->dropConstrainedForeignId('coordinator_member_id');
            $table->dropColumn([
                'preacher_type',
                'preacher_phone',
                'coordinator_type',
                'coordinator_name',
                'coordinator_phone',
            ]);
        });
    }
};
