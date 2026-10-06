<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('profile_photo_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('resume_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('designation')->nullable();
            $table->string('department')->nullable()->index();
            $table->string('college')->nullable();
            $table->string('qualification')->nullable();
            $table->decimal('experience_years', 4, 1)->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('city', 100)->nullable()->index();
            $table->timestamps();
        });

        Schema::create('faculty_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->string('degree');
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->foreignId('certificate_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('faculty_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('organization');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('faculty_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->string('skill', 100)->index();
            $table->timestamps();
            $table->unique(['faculty_profile_id', 'skill']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_skills');
        Schema::dropIfExists('faculty_experience');
        Schema::dropIfExists('faculty_education');
        Schema::dropIfExists('faculty_profiles');
    }
};
