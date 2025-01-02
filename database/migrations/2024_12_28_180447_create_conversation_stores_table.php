<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('conversation_stores');
        
        Schema::create('conversation_stores', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('message_sid')->nullable()->unique();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->text('message')->nullable();
            $table->string('status')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('phone');
            $table->index('created_at');
            $table->index(['phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_stores');
    }
};
