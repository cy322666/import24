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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('amo_status_id')->nullable();
            $table->integer('b24_status_id')->nullable();
            $table->integer('b24_status_name')->nullable();
            $table->boolean('is_put')->default(false);

            $table->index('is_put');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
};
