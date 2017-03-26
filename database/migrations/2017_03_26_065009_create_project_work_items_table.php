<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectWorkItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_work_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id');
            $table->integer('unit_id');
            $table->integer('cost_type_id');
            $table->string('name', 20);
            $table->timestamps();
        });

        Schema::create('project_work_project_work_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_work_id');
            $table->integer('project_work_item_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['project_work_id', 'project_work_item_id'], 'idx_p_work_p_work_item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_work_project_work_item');
        Schema::dropIfExists('project_work_items');
    }
}
