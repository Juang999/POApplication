<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalClothesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
            $table->string(ist)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clothes');
    }
}
