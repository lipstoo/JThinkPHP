<?php

use JThink\Core\Migration;
use JThink\Core\Schema;
use JThink\Core\Blueprint;

class CreateUsersTable extends Migration {
    public function up() {
        Schema::create('users', function(Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('users');
    }
}
