<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_tagihan',
        'jumlah',
    ];

    public function periode()
    {
        return $this->belongsTo(periode_tagihan::class, 'periode_tagihan', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function potongan()
    {
        return $this->hasMany(Potong::class);
    }
}
