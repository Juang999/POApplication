<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdditionalColumnsClothesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->unsignedBigInteger('product_order')->after('entity_name')->nullable();
            $table->string('category')->after('product_order')->nullable();
            $table->string('grade')->after('article_name')->nullable();
            $table->string('color_code')->after('color')->nullable();
            $table->unsignedBigInteger('style_id')->after('type_id')->nullable();
            $table->unsignedBigInteger('sub_style_id')->after('style_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
