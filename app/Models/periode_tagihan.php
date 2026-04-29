<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class periode_tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'penagih_id',
        'periode'
    ];

    public function penagih()
    {
        return $this->belongsTo(Penagih::class);
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'periode_id','id');
    }

    public function potongan()
    {
        return $this->hasManyThrough(
            Potong::class,
            Tagihan::class,
            'periode_id',
            'tagihan_id',
            'id',
            'id'
        );
    }
}
