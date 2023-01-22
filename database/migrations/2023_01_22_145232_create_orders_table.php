<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('customer_id')->nullable()->constrained()
                ->nullOnDelete()->cascadeOnUpdate();
            $table->float('amount', 16)->default(0);
            $table->float('discount', 0)->nullable();
            $table->string('status')->default('unpaid')->comment('paid, part paid, unpaid');
            $table->float('amount_paid', 16)->default(0);
            $table->softDeletes();
            $table->timestamp('paid_at')->nullable();
            $table->float('subtotal', 16)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
