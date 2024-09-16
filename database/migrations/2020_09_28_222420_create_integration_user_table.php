<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('integration_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('integration_id')
                ->references('id')
                ->on('integrations')
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
        Schema::dropIfExists('integration_user');
    }
}
