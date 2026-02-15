<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kijiji_filters', function (Blueprint $table) {
            $table->id();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('min_price')->nullable();
            $table->integer('max_price')->nullable();
            $table->integer('min_year')->nullable();
            $table->integer('max_year')->nullable();
            $table->integer('max_km')->nullable();
            $table->string('location')->default('ontario');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kijiji_filters');
    }
};
