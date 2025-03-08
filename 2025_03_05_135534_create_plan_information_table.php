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
        Schema::create('plan_information', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('applicable_type',['Teacher','Student']);
            $table->enum('currency',['Indian Rupees']);
            $table->boolean('status')->nullable();
            $table->boolean('popular')->nullable();
            $table->integer('display_order')->nullable(); 
            $table->string('upload_file')->nullable();
            //duration & features
            $table->enum('duration',['yearly']);
            $table->integer('amount')->nullable();
            //full plan
            $table->boolean('full_view_access_to_all_courses')->nullable();
            $table->boolean('full_create_own_courses')->nullable();
            $table->boolean('full_generate_custom_output')->nullable();
            $table->boolean('full_download_as_Doc_PPT')->nullable();
            $table->boolean('full_share_courses_to_other_users')->nullable();
            $table->boolean('full_course_edit_rights_to_multiple_users')->nullable();
            $table->boolean('full_custom_portal')->nullable();
            $table->integer('full_view_access_limit')->nullable();
            $table->string('full_view_access_text')->nullable();
            $table->integer('full_image2text_limit')->nullable();
            $table->string('full_image2text_text')->nullable();
            $table->integer('full_tokens_limit')->nullable();
            $table->string('full_tokens_text')->nullable();
            $table->integer('full_pdf_upload_limit')->nullable();
            $table->string('full_pdf_upload_text')->nullable();
            //trial
            $table->boolean('trial_available')->nullable();
            $table->integer('trial_days')->nullable();
            $table->boolean('trial_view_access_to_all_courses')->nullable();
            $table->boolean('trial_create_own_courses')->nullable();
            $table->boolean('trial_generate_custom_output')->nullable();
            $table->boolean('trial_download_as_Doc_PPT')->nullable();
            $table->boolean('trial_share_courses_to_other_users')->nullable();
            $table->boolean('trial_course_edit_rights_to_multiple_users')->nullable();
            $table->boolean('trial_custom_portal')->nullable();
            $table->integer('trial_full_access_limit')->nullable();
            $table->integer('trial_image2text_limit')->nullable();
            $table->integer('trial_tokens_limit')->nullable();
            $table->integer('trial_pdf_upload_limit')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_information');
    }
};
