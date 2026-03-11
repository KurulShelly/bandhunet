<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Alumni;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PelacakanController;

Route::get('/', function () {

    // Total alumni
    $total = Alumni::count();

    // Status pelacakan
    $belum = Alumni::whereRaw("LOWER(status_pelacakan) = 'belum dilacak'")->count();

    $teridentifikasi = Alumni::whereRaw("LOWER(status_pelacakan) = 'teridentifikasi'")->count();

    $verifikasi = Alumni::whereRaw("
        LOWER(status_pelacakan) = 'perlu verifikasi'
        OR LOWER(status_pelacakan) = 'perlu identifikasi'
    ")->count();

    $tidak_ditemukan = Alumni::whereRaw("LOWER(status_pelacakan) = 'tidak ditemukan'")->count();


    /*
    |--------------------------------------------------------------------------
    | Statistik Alumni per Prodi
    |--------------------------------------------------------------------------
    | Alumni dengan status Tidak Ditemukan tidak dihitung
    */

    $prodi = Alumni::select('prodi', DB::raw('count(*) as total'))
        ->whereRaw("LOWER(status_pelacakan) != 'tidak ditemukan'")
        ->groupBy('prodi')
        ->get();


    return view('dashboard', compact(
        'total',
        'belum',
        'teridentifikasi',
        'verifikasi',
        'tidak_ditemukan',
        'prodi'
    ));
});


/*
|--------------------------------------------------------------------------
| CRUD Alumni
|--------------------------------------------------------------------------
*/

Route::resource('alumni', AlumniController::class);


/*
|--------------------------------------------------------------------------
| Pelacakan Alumni
|--------------------------------------------------------------------------
*/

Route::get('/lacak/{id}', [PelacakanController::class, 'lacak'])
    ->name('alumni.lacak');