<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('financialplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('funds');
            $table->string('programs');
            $table->string('projects')->nullable();
            $table->string('activity')->nullable();
            $table->string('description')->nullable();
            $table->string('expense_class');
            $table->string('account_title');

            $table->year('year');
            $table->decimal('amount', 15, 2)->default(0);

            $table->decimal('q1', 15, 2)->default(0);
            $table->decimal('q2', 15, 2)->default(0);
            $table->decimal('q3', 15, 2)->default(0);
            $table->decimal('q4', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('r_center')->nullable();
            $table->text('form_rn')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financialplan');
    }
};
