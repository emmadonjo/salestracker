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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->nullable()->constrained()
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('order_id')->nullable()->constrained()
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('quantity')->default(0);
            $table->float('amount', 16)->default(0);
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
        Schema::dropIfExists('items');
    }
};
