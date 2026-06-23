<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // e.g. "M-Pesa Till", "Equity Bank", "PayPal"
            $table->string('type');                    // mpesa, bank, paypal, cash, other
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('phone_number')->nullable(); // for M-Pesa
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('swift_code')->nullable();
            $table->text('instructions')->nullable();   // "Send to 0712345678 - Name: John Doe"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};