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
        Schema::create('c_f_deals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->boolean('isRequired')->nullable();
            $table->boolean('isReadOnly')->nullable();
            $table->boolean('isImmutable')->nullable();
            $table->boolean('isMultiple')->nullable();
            $table->boolean('isDynamic')->nullable();
            $table->string('title')->nullable();
            $table->string('listLabel')->nullable();
            $table->string('formLabel')->nullable();
            $table->string('filterLabel')->nullable();
            $table->json('settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_f_deals');
    }
};
