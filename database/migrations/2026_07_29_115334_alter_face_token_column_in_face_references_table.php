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
        Schema::table('face_references', function (Blueprint $table) {
            $table->dropUnique('face_references_face_token_unique');
            $table->text('face_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('face_references', function (Blueprint $table) {
            $table->string('face_token', 255)->nullable()->change();
        });
    }
};
