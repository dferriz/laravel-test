<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFeatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('product_feature', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('feature_id')->unsigned();
            $table->integer('feature_value_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('product')
                ->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('feature')
                ->onDelete('cascade');
            $table->foreign('feature_value_id')->references('id')->on('feature_value')
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
        Schema::dropIfExists('product_feature');
    }
}
