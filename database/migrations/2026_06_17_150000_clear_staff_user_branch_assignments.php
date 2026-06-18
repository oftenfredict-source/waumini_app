<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Staff branch assignment is optional and explicit — do not inherit HQ from data migration.
        DB::table('users')->update(['branch_id' => null]);
    }

    public function down(): void
    {
        //
    }
};
