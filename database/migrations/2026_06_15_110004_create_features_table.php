<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module');
            $table->timestamps();
        });

        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('subscription_packages')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->json('limits')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_features');
        Schema::dropIfExists('features');
    }
};
