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
        Schema::create('assessment_headers', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_name');
            $table->string('assessment_type');
            $table->integer('assessment_duration');
            $table->time('assessment_time');
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('medium_id');
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->text('chapter_id')->nullable(); // Stored as comma-separated values
            $table->text('topic_id')->nullable(); // Stored as comma-separated values
            $table->enum('assessment_level', ['beginner', 'intermediate', 'expert']);
            
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('board_id')->references('id')->on('boards')->onDelete('cascade');
            $table->foreign('medium_id')->references('id')->on('mediums')->onDelete('cascade');
            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('course_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_headers');
    }
};
