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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_on_document_upload')->default(true);
            $table->boolean('notify_on_document_update')->default(true);
            $table->boolean('notify_on_document_delete')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_on_document_upload',
                'notify_on_document_update',
                'notify_on_document_delete'
            ]);
        });
    }
};
