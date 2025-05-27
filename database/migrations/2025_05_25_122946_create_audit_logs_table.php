<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('action'); // upload, update, delete, preview, dll
            $table->text('description')->nullable();
            $table->timestamps();

            // Optional foreign keys if ada tabel users dan documents
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
