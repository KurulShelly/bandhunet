<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\RiwayatPelacakan;

class PelacakanController extends Controller
{

    public function lacak($id)
    {

        $alumni = Alumni::findOrFail($id);

        // Query pencarian alumni
        $query1 = $alumni->nama . " Universitas Muhammadiyah Malang";
        $query2 = $alumni->nama . " " . $alumni->prodi;
        $query3 = $alumni->nama . " LinkedIn";

        // Simulasi hasil pencarian
        $hasil = [

            [
                'nama' => $alumni->nama,
                'jabatan' => 'Software Engineer',
                'instansi' => 'Tech Company',
                'sumber' => 'LinkedIn',
                'status' => 'Kemungkinan Kuat',
                'confidence' => 90
            ],

            [
                'nama' => $alumni->nama,
                'jabatan' => 'Research Assistant',
                'instansi' => 'Universitas',
                'sumber' => 'Google Scholar',
                'status' => 'Perlu Verifikasi',
                'confidence' => 70
            ]

        ];

        /*
        |--------------------------------------------------------------------------
        | Simpan Riwayat Pelacakan
        |--------------------------------------------------------------------------
        */

        foreach($hasil as $h){

            RiwayatPelacakan::create([

                'alumni_id' => $alumni->id,
                'sumber' => $h['sumber'],
                'jabatan' => $h['jabatan'],
                'instansi' => $h['instansi'],
                'confidence_score' => $h['confidence']

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Update Status Alumni
        |--------------------------------------------------------------------------
        */

        $alumni->status_pelacakan = "Teridentifikasi";
        $alumni->save();

        /*
        |--------------------------------------------------------------------------
        | Kirim Data ke View
        |--------------------------------------------------------------------------
        */

        return view('pelacakan.hasil', compact(
            'alumni',
            'query1',
            'query2',
            'query3',
            'hasil'
        ));

    }

}