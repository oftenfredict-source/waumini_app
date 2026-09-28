<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('google_file_id')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status', 20);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backup_logs');
    }
};
