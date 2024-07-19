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

        $pegawai = Pegawai::where('nik', $row[3])->first();
        $pegawai_id = $pegawai->id;

        return new Gaji([
            'bulan' => $row[1],
            'tahun' => $row[2],
            'nominal' => $row[4],
            'pegawai_id' => $pegawai_id,
        ]);
    }
}
