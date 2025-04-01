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
        Schema::create('links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid', 255);
            $table->string('ukey', 255);
            $table->string('proposal_no', 200);
            $table->integer('source'); // Removed length (10)
            $table->integer('product_id'); // Removed length (10)
            $table->string('product_name', 200);
            $table->mediumText('params')->nullable();
            $table->text('video_url');
            $table->text('consent_image_url')->nullable();
            $table->text('disagree_screens')->nullable();
            $table->text('reg_photo_url')->nullable();
            $table->text('transcript_pdf_url')->nullable();
            $table->integer('client_id'); // Removed length (10)
            $table->integer('expiry'); // Removed length (10)
            $table->string('link', 1000);
            $table->string('link_short', 255);
            $table->string('otp', 50)->nullable();
            $table->text('response')->nullable();
            $table->tinyInteger('complete_status')->nullable()->default(0);
            $table->tinyInteger('disagree_status')->nullable()->default(0);
            $table->tinyInteger('sftp_status')->default(0);
            $table->integer('version'); // Removed length (10)
            $table->tinyInteger('status')->default(0);
            $table->dateTime('completed_on')->nullable();
            $table->dateTime('created_on')->nullable();
            $table->dateTime('updated_on')->nullable();
            $table->text('device')->nullable();
            $table->timestamps(); // Adds `created_at` and `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
