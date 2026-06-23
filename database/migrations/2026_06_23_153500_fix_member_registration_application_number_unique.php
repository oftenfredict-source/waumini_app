<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'member_registration_applications_application_number_unique';

    private const NEW_UNIQUE = 'mra_church_app_num_uniq';

    public function up(): void
    {
        if ($this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('member_registration_applications', function (Blueprint $table) {
                $table->dropUnique(self::OLD_UNIQUE);
            });
        }

        if (! $this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('member_registration_applications', function (Blueprint $table) {
                $table->unique(['church_id', 'application_number'], self::NEW_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex(self::NEW_UNIQUE)) {
            Schema::table('member_registration_applications', function (Blueprint $table) {
                $table->dropUnique(self::NEW_UNIQUE);
            });
        }

        if (! $this->hasIndex(self::OLD_UNIQUE)) {
            Schema::table('member_registration_applications', function (Blueprint $table) {
                $table->unique('application_number', self::OLD_UNIQUE);
            });
        }
    }

    private function hasIndex(string $name): bool
    {
        return DB::select(
            'SHOW INDEX FROM member_registration_applications WHERE Key_name = ?',
            [$name]
        ) !== [];
    }
};
