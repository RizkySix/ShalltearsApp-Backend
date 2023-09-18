<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name' , 50);
            $table->string('last_name' , 50);
            $table->string('username' , 50)->unique();
            $table->string('email')->unique();
            $table->string('bio')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('foto_profile')->nullable();
            $table->string('address' , 100 )->nullable();
            $table->string('phone_number' , 50)->nullable()->unique();
            $table->json('notification_list')->nullable();
            $table->integer('readed_notification')->default(0);
            $table->string('reset_password_token')->nullable();
           // $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

