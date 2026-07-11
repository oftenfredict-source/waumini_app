<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50);
            $table->string('permission', 100);
            $table->timestamps();

            $table->unique(['church_id', 'role', 'permission'], 'church_role_permission_unique');
            $table->index(['church_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_role_permissions');
    }
};
