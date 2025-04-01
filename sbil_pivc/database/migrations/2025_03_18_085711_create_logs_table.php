<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {

            $table->increments('id'); // INT(10) AUTO_INCREMENT
            $table->string('slug', 100);
            $table->text('input_msg');
            $table->text('output_msg')->nullable();
            $table->tinyInteger('fail_status')->default(0)->comment('0 = Failure, 1 = Success');
            $table->tinyInteger('retry_status')->default(0)->comment('0 = Not retried, 1 = Retried');
            $table->dateTime('created_on')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
