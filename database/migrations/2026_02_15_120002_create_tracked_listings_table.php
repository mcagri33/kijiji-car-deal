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
        Schema::create('tracked_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kijiji_listing_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_tracking')->default(true);
            $table->integer('last_notified_price');
            $table->boolean('notify_on_price_drop')->default(true);
            $table->boolean('notify_on_sold')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracked_listings');
    }
};
