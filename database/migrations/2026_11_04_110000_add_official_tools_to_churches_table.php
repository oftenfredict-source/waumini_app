<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->string('seal_path')->nullable()->after('logo_path');
            $table->string('pastor_signature_path')->nullable()->after('seal_path');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['seal_path', 'pastor_signature_path']);
        });
    }
};
