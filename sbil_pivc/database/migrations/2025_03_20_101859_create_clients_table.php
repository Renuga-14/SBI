<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('ckey', 200);
            $table->string('short_name', 200);
            $table->string('full_name', 250);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->timestamp('created_on')->useCurrent();
            $table->timestamp('updated_on')->nullable()->useCurrentOnUpdate();
        });
    }
  
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
