<?php

use App\Models\periode_tagihan;
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
        Schema::table('tagihans', function(Blueprint $table){
            $table->dropColumn('penagih_id');
            $table->foreignIdFor(periode_tagihan::class, 'periode_tagihan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
