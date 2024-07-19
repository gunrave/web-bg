<?php

use App\Models\Pegawai;
use App\Models\Tagihan;
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
        Schema::create('potongs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tagihan::class, 'tagihan_id');
            $table->boolean('isGapok');
            $table->integer('nominal');
            $table->boolean('sukses');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potongs');
    }
};
