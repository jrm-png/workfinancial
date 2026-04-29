<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            // Human-readable reference number (e.g. WPFP-2026-00001)
            $table->string('form_ref')->unique();

            // Planning year
            $table->year('year');

            // Optional but recommended for filtering & permissions
            $table->unsignedBigInteger('department_id')->nullable();

            // Workflow status
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'rejected'
            ])->default('draft');

            // Ownership & workflow tracking
            $table->unsignedBigInteger('created_by');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('year');
            $table->index('status');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
