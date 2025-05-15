<?php

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
        Schema::create('weekly_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('week');
            $table->foreignId('segment_id')->constrained();
            $table->foreignId('machine_id')->nullable()->constrained();
            $table->decimal('total_stop_time', 8, 2);
            $table->integer('interventions_count');
            $table->timestamps();

            $table->unique(['year', 'week', 'segment_id', 'machine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_stats');
    }
};
