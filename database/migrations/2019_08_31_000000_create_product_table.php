<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('product', function (Blueprint $table) {
            $table->integer('id')->unsigned()->primary();
            $table->string('name', 255);
            $table->binary('overview')->nullable(true);
            $table->decimal('price', 12, 2)->nullable(true);
            $table->decimal('price_previous', 12, 2)->nullable(true);
            $table->boolean('has_stock')->default(false);
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
        Schema::dropIfExists('product');
    }
}
