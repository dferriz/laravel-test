<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeatureValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('feature_value', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->integer('feature_id')->unsigned();
            $table->string('value', 255);
            $table->foreign('feature_id')
                ->references('id')->on('feature')
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
        Schema::table('feature_value', function (Blueprint $table) {
            //
        });
    }
}
