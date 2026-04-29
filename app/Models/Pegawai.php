<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nik',
        'nama',
        'isActive',
        'norek',
        'golpang',
    ];

    public function gaji()
    {
        return $this->hasMany(Gaji::class);
    }

    public function tunker()
    {
        return $this->hasMany(Tunker::class);
    }
}
