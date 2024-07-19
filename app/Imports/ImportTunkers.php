<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\Tunker;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportTunkers implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $pegawai = Pegawai::where('nik', $row[2])->first();
        if(!isset($pegawai)){
            return null;
        }
        $pegawai_id = $pegawai->id;

        return new Tunker([
            'bulan' => $row[12],
            'tahun' => $row[13],
            'nominal' => $row[10],
            'pegawai_id' => $pegawai_id,
        ]);
    }
}
