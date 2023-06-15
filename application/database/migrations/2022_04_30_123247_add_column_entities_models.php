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
        Schema::table('contacts', function($table) {
            $table->integer('entity_id')->nullable();
        });

        Schema::table('leads', function($table) {
            $table->integer('entity_id')->nullable();
        });

        Schema::table('companies', function($table) {
            $table->integer('entity_id')->nullable();
        });

        Schema::table('deals', function($table) {
            $table->integer('entity_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
