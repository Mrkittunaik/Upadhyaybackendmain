<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('status', 20)->default('submitted')->index();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
            $table->unique(['job_post_id', 'user_id']); // duplicate application protection
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
