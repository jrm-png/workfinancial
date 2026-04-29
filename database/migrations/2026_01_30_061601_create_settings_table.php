<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up() {
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->date('submission_start')->nullable();
    $table->date('submission_end')->nullable();
    $table->boolean('is_viewing_open')->default(false);
    $table->timestamps();
});

// We add a column to users or a separate table for overrides
Schema::table('users', function (Blueprint $table) {
    $table->boolean('can_override_submission')->default(false);
});}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
