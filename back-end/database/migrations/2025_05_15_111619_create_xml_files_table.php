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
        Schema::create('xml_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->date('from_date');
            $table->date('to_date');
            $table->date('print_date');
            $table->decimal('total_stop_time', 8, 2);
            $table->timestamp('imported_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_files');
    }
};
