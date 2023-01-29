<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("title");

            $table->timestamp("startDate")->nullable();
            $table->timestamp("endDate")->nullable();
            $table->bigInteger("organizer");
            $table->longText("description");
            $table->decimal("revenue", 20, 2);
  
            $table->boolean("active");
            $table->enum("status", ['LIVE', 'FINISHED', 'PENDING', "REVIEWING"]);
            $table->longText("cover_image");
            $table->longText("extra_images");
            $table->longText("tags")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
