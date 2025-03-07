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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('contact_id')->unsigned();
            $table->bigInteger('layout_id')->unsigned();
            $table->bigInteger('template_id')->unsigned();
            $table->enum('message_type', ['email', 'sms']);
            $table->string('message_from', 255)->nullable();
            $table->string('message_to', 255);
            $table->string('message_subject', 255)->nullable();
            $table->longText('message_body');
            $table->enum('message_status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->timestamp('message_sent_at')->nullable();
            $table->longText('message_response')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
