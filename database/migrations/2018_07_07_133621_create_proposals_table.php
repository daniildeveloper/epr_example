<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique()->nullable();
            $table->integer('creator_id');
            $table->string('client');
            $table->string('client_phone');
            $table->integer('object')->nullable();
            $table->boolean('is_hot')->nullable();
            $table->boolean('warranty_case')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_with_docs')->default(false);
            $table->integer('tax_type')->nullable();
            $table->float('tax')->nullable();
            $table->date('client_deadline');
            $table->date('workers_deadline');
            $table->integer('status_id')->default(1);
            $table->integer('partner_payment')->nullable();
            $table->text('partner_notes')->nullable();
            $table->boolean('closed')->default(false);
            $table->boolean('accepted')->default(false);
            $table->integer('accounting_period_end_id')->nullable();
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
        Schema::dropIfExists('proposals');
    }
}
