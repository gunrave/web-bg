<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'isActive'
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
