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
        Schema::create('kijiji_listings', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('title');
            $table->integer('price');
            $table->integer('year')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('location')->nullable();
            $table->text('url');
            $table->enum('status', ['active', 'sold'])->default('active');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('price');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kijiji_listings');
    }
};
