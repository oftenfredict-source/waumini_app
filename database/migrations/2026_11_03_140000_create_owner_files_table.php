<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('category', 30)->default('other')->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['files.view', 'files.manage'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::where('name', 'owner')->where('guard_name', 'web')->first();
        if ($owner) {
            $owner->givePermissionTo(['files.view', 'files.manage']);
        }

        $staff = Role::where('name', 'staff')->where('guard_name', 'web')->first();
        if ($staff) {
            $staff->givePermissionTo(['files.view']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_files');

        if (class_exists(Permission::class)) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            Permission::whereIn('name', ['files.view', 'files.manage'])->delete();
        }
    }
};
