<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('attendance_time');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('distance_meter')->default(0);
            $table->boolean('is_within_radius')->default(false);
            $table->float('face_match_score')->nullable();
            $table->boolean('is_face_verified')->default(false);
            $table->string('photo_path');
            $table->unsignedBigInteger('photo_size_kb')->nullable();
            $table->enum('status', ['valid', 'rejected', 'manual_request'])->default('valid');
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
