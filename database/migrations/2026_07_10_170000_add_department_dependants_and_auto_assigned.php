<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('department_member', function (Blueprint $table) {
            $table->boolean('auto_assigned')->default(false)->after('role');
        });

        Schema::create('department_dependant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_dependant_id')->constrained('member_dependants')->cascadeOnDelete();
            $table->boolean('auto_assigned')->default(true);
            $table->timestamps();

            $table->unique(['department_id', 'member_dependant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_dependant');

        Schema::table('department_member', function (Blueprint $table) {
            $table->dropColumn('auto_assigned');
        });
    }
};
