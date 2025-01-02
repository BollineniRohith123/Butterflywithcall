<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordingsTable extends Migration
{
    public function up()
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid');
            $table->string('recording_sid');
            $table->string('phone_number');  // Added phone number field
            $table->string('recording_url')->nullable();
            $table->string('recording_status');
            $table->integer('recording_duration')->default(0);
            $table->integer('recording_channels')->default(1);
            $table->string('recording_source')->nullable();
            $table->decimal('recording_price', 10, 4)->default(0);
            $table->string('recording_price_unit', 3)->default('USD');
            $table->integer('recording_error_code')->nullable();
            $table->text('recording_encryption_details')->nullable();
            $table->string('recording_media_type')->nullable();
            $table->string('recording_track')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recordings');
    }
}
