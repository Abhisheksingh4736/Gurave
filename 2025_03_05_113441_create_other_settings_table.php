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
        Schema::create('other_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('token_value_per_rupee')->nullable();
            $table->string('buy_token_worth_rupees')->nullable();
            $table->boolean('allow_share')->nullable();
            $table->boolean('allow_download')->nullable();
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
        Schema::dropIfExists('other_settings');
    }
};
