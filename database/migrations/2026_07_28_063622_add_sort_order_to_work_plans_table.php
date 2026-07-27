<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workplan', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->default(0)->after('form_id');
        });
    }

    public function down(): void
    {
        Schema::table('work_plans', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};