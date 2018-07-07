<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('answered_id');
            $table->string('component_type');
            $table->string('component_id');
            $table->integer('expected_rests');
            $table->integer('expected_sum');
            $table->integer('real_sum');
            $table->integer('real_rest');
            $table->integer('diffirence')->nullable();
            $table->integer('diffirence_sum')->nullable();
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
        Schema::dropIfExists('inventories');
    }
}
