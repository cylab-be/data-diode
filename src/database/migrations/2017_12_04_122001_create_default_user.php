<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class CreateDefaultUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::create(array(
            "email" => env('DEFAULT_ADMIN_LOGIN', 'admin@admin'),
            "name" => "Admin",
            "password" => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'admin'))
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::where("email", "=", "admin@admin")->first()->delete();
    }
}
