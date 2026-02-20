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
        Schema::table('room_type_facilities', function (Blueprint $table) {
            $table->dropForeign(['facilitiy_id']);

            // 2. Rename kolom dari 'facilitiy_id' ke 'facility_id'
            $table->renameColumn('facilitiy_id', 'facility_id');

            // 3. Pasang Foreign Key yang baru ke tabel facilities
            $table->foreign('facility_id')
                ->references('id')
                ->on('facilities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_type_facilities', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->renameColumn('facility_id', 'facilitiy_id');
            $table->foreign('facilitiy_id')
                ->references('id')
                ->on('facilities')
                ->onDelete('cascade');
        });
    }
};
