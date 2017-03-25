<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_works', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->decimal('amount', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->integer('engineering_type_id');
            $table->integer('project_id');
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
        Schema::dropIfExists('project_works');
    }
}
