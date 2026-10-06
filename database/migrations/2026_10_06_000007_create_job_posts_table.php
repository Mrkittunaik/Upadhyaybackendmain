<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named job_posts (not "jobs") to avoid clashing with Laravel's queue "jobs" table.
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->nullOnDelete();
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('image_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('title')->index();
            $table->text('description');
            $table->string('department')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('city', 100)->nullable()->index();
            $table->string('employment_type', 30)->nullable()->index();
            $table->string('experience')->nullable();
            $table->string('salary')->nullable();
            $table->string('education')->nullable();
            $table->date('application_deadline')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'branch_id', 'status']);
        });

        Schema::create('job_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->string('skill', 100)->index();
            $table->unique(['job_post_id', 'skill']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_skills');
        Schema::dropIfExists('job_posts');
    }
};
