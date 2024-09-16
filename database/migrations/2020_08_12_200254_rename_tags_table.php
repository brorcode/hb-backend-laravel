<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropForeign('tags_category_pointer_id_foreign');
        });

        Schema::rename('tags', 'category_pointer_tags');

        Schema::table('category_pointer_tags', function (Blueprint $table) {
            $table->foreign('category_pointer_id')
                ->references('id')
                ->on('category_pointers')
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
        Schema::table('category_pointer_tags', function (Blueprint $table) {
            $table->dropForeign('category_pointer_tags_category_pointer_id_foreign');
        });

        Schema::rename('category_pointer_tags', 'tags');

        Schema::table('tags', function (Blueprint $table) {
            $table->foreign('category_pointer_id')
                ->references('id')
                ->on('category_pointers')
                ->onDelete('cascade')
            ;
        });
    }
}
