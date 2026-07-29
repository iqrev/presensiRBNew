<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable()->after('name');
            $table->string('jabatan')->nullable()->after('nik');
            $table->string('department')->nullable()->after('jabatan');
            $table->string('phone')->nullable()->after('department');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('phone');
            $table->timestamp('biometric_consent_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'jabatan', 'department', 'phone', 'status', 'biometric_consent_at']);
        });
    }
};
