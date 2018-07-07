<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalWaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal_wares', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proposal_id');
            $table->integer('ware_id');
            $table->float('price_per_count');
            $table->integer('count');
            $table->string('color')->nullable();
            $table->float('color_price');
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
        Schema::dropIfExists('proposal_wares');
    }
}
