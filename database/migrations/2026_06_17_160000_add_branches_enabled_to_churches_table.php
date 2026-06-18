<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->boolean('branches_enabled')->default(false)->after('currency');
        });

        DB::table('churches')
            ->whereIn('id', DB::table('church_branches')->distinct()->pluck('church_id'))
            ->update(['branches_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('branches_enabled');
        });
    }
};
