<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\RiwayatPelacakan;

class PelacakanController extends Controller
{

    public function lacak($id)
    {

        $alumni = Alumni::findOrFail($id);


        $query1 = $alumni->nama . " Universitas Muhammadiyah Malang";
        $query2 = $alumni->nama . " Informatika UMM";
        $query3 = $alumni->nama . " site:scholar.google.com";
        $query4 = $alumni->nama . " software engineer Malang";




        $databaseInternet = [

            [
                'nama' => 'Budi Santoso',
                'jabatan' => 'Software Engineer',
                'instansi' => 'Gojek',
                'sumber' => 'LinkedIn',
                'lokasi' => 'Jakarta',
                'bidang' => 'Software Engineering',
                'tahun' => 2024,
                'link' => 'https://linkedin.com',
                'universitas' => 'Universitas Muhammadiyah Malang'
            ],

            [
                'nama' => 'Siti Rahma',
                'jabatan' => 'Data Scientist',
                'instansi' => 'Shopee',
                'sumber' => 'LinkedIn',
                'lokasi' => 'Singapore',
                'bidang' => 'Artificial Intelligence',
                'tahun' => 2023,
                'link' => 'https://linkedin.com',
                'universitas' => 'Universitas Brawijaya'
            ],

            [
                'nama' => 'Ahmad Fauzi',
                'jabatan' => 'Research Assistant',
                'instansi' => 'Universitas',
                'sumber' => 'Google Scholar',
                'lokasi' => 'Indonesia',
                'bidang' => 'Machine Learning',
                'tahun' => 2023,
                'link' => 'https://scholar.google.com',
                'universitas' => 'Universitas Muhammadiyah Malang'
            ]

        ];



        $hasil = [];

        foreach ($databaseInternet as $data) {

            
            if ($data['universitas'] != "Universitas Muhammadiyah Malang") {
                continue;
            }

            similar_text(
                strtolower($alumni->nama),
                strtolower($data['nama']),
                $percent
            );

            $confidence = round($percent);

            
            if ($confidence >= 50) {

                $data['confidence'] = $confidence;

                
                if ($data['sumber'] == "LinkedIn") {
                    $data['query'] = $query1;
                } elseif ($data['sumber'] == "Google Scholar") {
                    $data['query'] = $query3;
                } else {
                    $data['query'] = $query2;
                }

                $hasil[] = $data;
            }
        }


        if (empty($hasil)) {

            $alumni->status_pelacakan = "Tidak Ditemukan";
            $alumni->save();

            return view('pelacakan.hasil', compact(
                'alumni',
                'query1',
                'query2',
                'query3',
                'query4',
                'hasil'
            ));
        }




        foreach ($hasil as $h) {

            RiwayatPelacakan::create([

                'alumni_id' => $alumni->id,
                'query' => $h['query'],
                'sumber' => $h['sumber'],
                'jabatan' => $h['jabatan'],
                'instansi' => $h['instansi'],
                'lokasi' => $h['lokasi'],
                'bidang' => $h['bidang'],
                'tahun' => $h['tahun'],
                'link' => $h['link'],
                'confidence_score' => $h['confidence']

            ]);
        }


        $maxScore = max(array_column($hasil, 'confidence'));

        if ($maxScore >= 80) {

            $status = "Teridentifikasi";

        } elseif ($maxScore >= 50) {

            $status = "Perlu Verifikasi";

        } else {

            $status = "Tidak Ditemukan";
        }

        $alumni->status_pelacakan = $status;
        $alumni->save();


        return view('pelacakan.hasil', compact(
            'alumni',
            'query1',
            'query2',
            'query3',
            'query4',
            'hasil'
        ));
    }
}