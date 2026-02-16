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

            // NIK boleh kosong dulu, nanti diisi user saat verifikasi
            $table->string('nik', 16)->unique()->nullable();

            // Gender & Tgl Lahir wajib nullable (Google gak kasih data ini)
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();

            // No HP boleh nullable (meskipun di service Anda isi '-', lebih bersih kalau null)
            $table->string('phone_number')->index()->nullable();

            // Alamat & Pekerjaan wajib nullable
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();

            // Kontak darurat wajib nullable (User isi nanti di edit profile)
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // KTP Path & Verified
            $table->string('ktp_path')->nullable();
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
