<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryPointerTagUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_pointer_tag_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_pointer_tag_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('category_pointer_tag_id')
                ->references('id')
                ->on('category_pointer_tags')
                ->onDelete('cascade')
            ;
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_pointer_tag_user');
    }
}
