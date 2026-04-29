<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workplan', function (Blueprint $table) {
            // This adds the link to the 'forms' table
            $table->foreignId('form_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });

        Schema::table('financialplans', function (Blueprint $table) {
            $table->foreignId('form_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('workplan', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->dropColumn('form_id');
        });

        Schema::table('financialplans', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->dropColumn('form_id');
        });
    }
};
