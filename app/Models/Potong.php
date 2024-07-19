<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Potong extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'isGapok',
        'nominal',
        'sukses',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }
}
