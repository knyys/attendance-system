<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_time_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correct_request_id')->constrained('correct_requests')->onDelete('cascade');
            $table->foreignId('break_time_id')->nullable()->constrained('break_times')->onDelete('set null');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('total_break_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_time_requests');
    }
}
