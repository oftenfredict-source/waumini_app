<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_assets', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('name');
            $table->uuid('batch_id')->nullable()->after('quantity');
            $table->index(['church_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('church_assets', function (Blueprint $table) {
            $table->dropIndex(['church_id', 'batch_id']);
            $table->dropColumn(['quantity', 'batch_id']);
        });
    }
};
