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
        Schema::create('company_custom_field', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('company_id')->index();
            $table->integer('cf_id')->index();
            $table->string('value')->nullable();
            $table->json('params')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_custom_field');
    }
};
