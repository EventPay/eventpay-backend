<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("profile_image")->nullable();
            $table->string('firstname');
            $table->string("lastname");
            $table->string("username")->unique();
            $table->string('email')->unique();
            $table->string("phone")->nullable();
            $table->boolean("suspended")->default(0);
            $table->enum("gender",['MALE','FEMALE']);
            $table->timestamp("phone_verified_at")->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean("organizer")->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
