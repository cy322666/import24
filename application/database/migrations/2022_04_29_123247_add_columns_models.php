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
        Schema::table('c_f_contacts', function($table) {
            $table->integer('field_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('name')->nullable();
        });

        Schema::table('c_f_leads', function($table) {
            $table->integer('field_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('name')->nullable();
        });

        Schema::table('c_f_companies', function($table) {
            $table->integer('field_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('name')->nullable();
        });

        Schema::table('c_f_deals', function($table) {
            $table->integer('field_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('name')->nullable();
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
