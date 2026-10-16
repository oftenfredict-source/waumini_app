<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaders', function (Blueprint $table) {
            if (! Schema::hasColumn('leaders', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaders', function (Blueprint $table) {
            if (Schema::hasColumn('leaders', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }
        });
    }
};
