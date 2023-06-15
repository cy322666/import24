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
        Schema::create('dev_events_leads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('event_id')->unique();
            $table->string('name')->nullable();
            $table->string('link')->nullable();
            $table->string('createdAt')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('price')->nullable();
            $table->string('contact_link')->nullable();
            $table->string('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dev_events_leads');
    }
};
