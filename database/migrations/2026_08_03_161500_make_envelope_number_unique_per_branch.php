<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'members_church_id_envelope_number_unique';

    private const NEW_UNIQUE = 'members_church_id_branch_id_envelope_number_unique';

    public function up(): void
    {
        if (! Schema::hasTable('members') || ! Schema::hasColumn('members', 'envelope_number')) {
            return;
        }

        if ($this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique(self::OLD_UNIQUE);
            });
        }

        if (Schema::hasColumn('members', 'branch_id') && ! $this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('members', function (Blueprint $table) {
                $table->unique(['church_id', 'branch_id', 'envelope_number'], self::NEW_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        if ($this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique(self::NEW_UNIQUE);
            });
        }

        if (! $this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('members', function (Blueprint $table) {
                $table->unique(['church_id', 'envelope_number'], self::OLD_UNIQUE);
            });
        }
    }

    private function hasIndex(string $name): bool
    {
        return DB::select(
            'SHOW INDEX FROM members WHERE Key_name = ?',
            [$name]
        ) !== [];
    }
};
