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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name')->index();
            $table->string('nik', 16)->unique();
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');
            $table->string('phone_number')->index();
            $table->text('address');
            $table->string('occupation');

            // Kontak darurat
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');

            $table->string('ktp_path');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
