<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->boolean('is_headquarters')->default(false);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pastor_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['church_id', 'code']);
            $table->index(['church_id', 'is_active']);
        });

        $tables = ['members', 'leaders', 'tithes', 'offerings', 'expenses', 'member_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('church_id')->constrained('church_branches')->nullOnDelete();
                $table->index('branch_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('church_id')->constrained('church_branches')->nullOnDelete();
            $table->index('branch_id');
        });

        $churches = DB::table('churches')->whereNull('deleted_at')->get();

        foreach ($churches as $church) {
            $branchId = DB::table('church_branches')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'church_id' => $church->id,
                'name' => $church->name.' - Makao Makuu',
                'code' => 'HQ',
                'is_headquarters' => true,
                'address' => $church->address,
                'city' => $church->city,
                'phone' => $church->phone,
                'email' => $church->email,
                'pastor_name' => $church->pastor_name,
                'logo_path' => $church->logo_path,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($tables as $tableName) {
                DB::table($tableName)
                    ->where('church_id', $church->id)
                    ->whereNull('branch_id')
                    ->update(['branch_id' => $branchId]);
            }
        }
    }

    public function down(): void
    {
        $tables = ['member_requests', 'expenses', 'offerings', 'tithes', 'leaders', 'users', 'members'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        Schema::dropIfExists('church_branches');
    }
};
