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
        Schema::table('workplan', function (Blueprint $table) {
        $table->json('strategic_perspective')->nullable();
        $table->json('strategic_objective')->nullable();
        $table->json('major_program')->nullable();
        $table->json('strategic_measure')->nullable();
        $table->text('strategic_initiatives')->nullable();
        $table->text('success_indicator')->nullable();
        $table->integer('year')->nullable();
        $table->string('q1')->nullable();
        $table->string('q2')->nullable();
        $table->string('q3')->nullable();
        $table->string('q4')->nullable();
        $table->string('total')->nullable();
        $table->text('remarks')->nullable();
        $table->text('r_center')->nullable();
        $table->text('form_rn')->nullable();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan', function (Blueprint $table) {
            //
        });
    }
};
