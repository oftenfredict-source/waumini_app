<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('attendance_records')
            ->select('church_id', 'source_type', 'source_id', 'member_id')
            ->whereNotNull('member_id')
            ->groupBy('church_id', 'source_type', 'source_id', 'member_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('attendance_records')
                ->where('church_id', $duplicate->church_id)
                ->where('source_type', $duplicate->source_type)
                ->where('source_id', $duplicate->source_id)
                ->where('member_id', $duplicate->member_id)
                ->orderBy('id')
                ->pluck('id');

            $ids->shift();
            if ($ids->isNotEmpty()) {
                DB::table('attendance_records')->whereIn('id', $ids)->delete();
            }
        }

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unique(
                ['church_id', 'source_type', 'source_id', 'member_id'],
                'attendance_member_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('attendance_member_source_unique');
        });
    }
};
