<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->integer('unit_id');
            $table->integer('cost_type_id');
            $table->timestamps();
        });

        Schema::create('work_work_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_id');
            $table->integer('work_item_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['work_id', 'work_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_work_item');
        Schema::dropIfExists('work_items');
    }
}
