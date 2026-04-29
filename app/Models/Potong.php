<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Pest\Laravel\get;

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

    public function getGapokAttribute()
    {
        $gapok = $this->where('isGapok', 1);
        return $gapok;
    }

    // public function getSuksesAttribute()
    // {
    //     return $this->where('sukses')
    // }

}
