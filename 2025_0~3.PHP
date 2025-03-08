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
        Schema::create('payment_invoice_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('gst_no')->nullable();;
            $table->string('gst_order_prefix')->nullable();;
            $table->string('gst_transaction_prefix')->nullable();;
            $table->integer('gst_retry_limit')->nullable();
            $table->boolean('gst_inclusive_tax')->nullable();
            $table->enum('gst_tax_class',['2','3','4','5'])->nullable();
           //custom portal
            $table->string('custom_order_prefix')->nullable();
            $table->string('custom_transaction_prefix')->nullable();
            $table->integer('custom_retry_limit')->nullable();
            $table->string('custom_razorpay_key_id')->nullable();
            $table->string('custom_razorpay_key_secret')->nullable();
            $table->boolean('custom_inclusive_tax')->nullable();
            $table->enum('custom_tax_class',['2','3','4','5'])->nullable();
            

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
        Schema::dropIfExists('payment_invoice_configuration');
    }
};
