<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEndpointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endpoint', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->string('entity', 75);
            $table->integer('entity_id')->unsigned();
            $table->string('endpoint', 255);
            $table->timestamp('last_check')->nullable();
            $table->boolean('is_checked')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('endpoint');
    }
}
