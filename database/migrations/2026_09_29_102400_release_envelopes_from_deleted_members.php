<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members') || ! Schema::hasColumn('members', 'envelope_number')) {
            return;
        }

        // Soft-deleted members must not keep envelope numbers reserved.
        DB::table('members')
            ->whereNotNull('deleted_at')
            ->update([
                'envelope_number' => null,
                'spouse_envelope_number' => null,
            ]);

        $deletedIds = DB::table('members')
            ->whereNotNull('deleted_at')
            ->pluck('id');

        if ($deletedIds->isEmpty()) {
            return;
        }

        // Surviving members must not keep a link/reservation to a deleted spouse.
        DB::table('members')
            ->whereNull('deleted_at')
            ->whereIn('spouse_member_id', $deletedIds->all())
            ->update([
                'spouse_member_id' => null,
                'spouse_envelope_number' => null,
            ]);
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
