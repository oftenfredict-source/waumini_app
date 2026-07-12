<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['church_services', 'special_events', 'attendance_records'];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'branch_id')) {
                    $table->foreignId('branch_id')
                        ->nullable()
                        ->after('church_id')
                        ->constrained('church_branches')
                        ->nullOnDelete();
                    $table->index('branch_id');
                }
            });
        }

        $churches = DB::table('churches')->whereNull('deleted_at')->get(['id']);

        foreach ($churches as $church) {
            $hqId = DB::table('church_branches')
                ->where('church_id', $church->id)
                ->where('is_headquarters', true)
                ->whereNull('deleted_at')
                ->value('id');

            if (! $hqId) {
                continue;
            }

            foreach ($tables as $tableName) {
                if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'branch_id')) {
                    continue;
                }

                DB::table($tableName)
                    ->where('church_id', $church->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $hqId]);
            }
        }
    }

    public function down(): void
    {
        foreach (['attendance_records', 'special_events', 'church_services'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'branch_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
