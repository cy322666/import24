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
        Schema::create('company_comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('company_id')->nullable();
            $table->integer('comment_id')->nullable();
            $table->text('text')->nullable();
            $table->string('created')->nullable();
            $table->integer('author_id')->nullable();
            $table->json('files')->nullable();
            $table->integer('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_comments');
    }
};
