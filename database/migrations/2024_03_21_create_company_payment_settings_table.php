<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('payment_manager')->default('XavexPay');
            $table->string('payment_domain_url');
            $table->string('payment_api_key');
            $table->string('payment_tenant_id');
            $table->string('payment_context')->default('Invoice');
            $table->string('payment_status')->default('CREATED');
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_payment_settings');
    }
}; 