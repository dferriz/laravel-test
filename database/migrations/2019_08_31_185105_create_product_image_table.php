<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('product_image', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->uuid('uuid')->unique();
            $table->string('file_name', 255);
            $table->boolean('is_cover')->default(false);
            $table->foreign('product_id')->references('id')->on('product')
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_image');
    }
}
