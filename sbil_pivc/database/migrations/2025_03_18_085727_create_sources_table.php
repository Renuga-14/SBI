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
        Schema::create('sources', function (Blueprint $table) {

            $table->increments('id'); // INT(10) AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 100);
            $table->text('desc'); // TEXT field
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->dateTime('created_on')->useCurrent();
            $table->dateTime('updated_on')->nullable()->default(null)->comment('NULL on update');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
