<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'departments_church_id_name_unique';

    private const NEW_UNIQUE = 'departments_church_id_branch_id_name_unique';

    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        if (! Schema::hasColumn('departments', 'branch_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('church_id')
                    ->constrained('church_branches')
                    ->nullOnDelete();
                $table->index('branch_id');
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

            DB::table('departments')
                ->where('church_id', $church->id)
                ->whereNull('branch_id')
                ->update(['branch_id' => $hqId]);
        }

        if ($this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropUnique(self::OLD_UNIQUE);
            });
        }

        if (! $this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('departments', function (Blueprint $table) {
                $table->unique(['church_id', 'branch_id', 'name'], self::NEW_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        if ($this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropUnique(self::NEW_UNIQUE);
            });
        }

        if (Schema::hasColumn('departments', 'branch_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        if (! $this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('departments', function (Blueprint $table) {
                $table->unique(['church_id', 'name'], self::OLD_UNIQUE);
            });
        }
    }

    private function hasIndex(string $name): bool
    {
        return DB::select(
            'SHOW INDEX FROM departments WHERE Key_name = ?',
            [$name]
        ) !== [];
    }
};
