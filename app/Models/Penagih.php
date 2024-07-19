<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penagih extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'isActive'
    ];

    public function tagihan()
    {
        return $this->hasManyThrough(
            Tagihan::class,
            periode_tagihan::class,
            'penagih_id',
            'periode_tagihan',
            'id',
            'id'
        );
    }

    public function periode()
    {
        return $this->hasMany(periode_tagihan::class);
    }
}
