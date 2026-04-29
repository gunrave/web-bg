<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Penagih extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'isActive',
        'rules',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
        ];
    }

    public function tagihan()
    {
        return $this->hasManyThrough(
            Tagihan::class,
            periode_tagihan::class,
            'penagih_id',
            'periode_id',
            'id',
            'id'
        );
    }

    public function periode()
    {
        return $this->hasMany(periode_tagihan::class);
    }
}
