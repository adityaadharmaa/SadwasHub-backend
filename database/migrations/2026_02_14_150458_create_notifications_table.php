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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');

            // Kategori: 'payment', 'booking_confirmation', 'reminder', 'announcement'
            $table->string('category')->index();

            // Channel: 'whatsapp', 'email', 'in_app'
            $table->string('channel');

            $table->string('title');
            $table->text('message');

            // Payload menyimpan data mentah (JSON) untuk keperluan debugging/retry
            $table->json('payload')->nullable();

            // Status pengiriman dari provider (misal: 'sent', 'delivered', 'failed')
            $table->string('delivery_status')->default('pending')->index();
            $table->text('error_log')->nullable(); // Simpan pesan error jika gagal kirim

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexing tambahan untuk query harian/bulanan
            $table->index(['created_at', 'delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
