<?php

namespace App\Imports;

use App\Models\Gaji;
use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class ImportGajis implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // $pegawai = Pegawai::where('nik', $row[3])->first();
        $pegawai = Pegawai::firstOrCreate(
            ['nik' => $row[8]],
            ['nama' => $row[9],
            'norek' => $row[15],
            'golpang' => substr($row[48], 0, 2),
            'isActive' => 1,
        ]);
        $pegawai_id = $pegawai->id;

        return new Gaji([
            'bulan' => $row[4],
            'tahun' => $row[5],
            'nominal' => $row[43],
            'pegawai_id' => $pegawai_id,
        ]);
    }
}
