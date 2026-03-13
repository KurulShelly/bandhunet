<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Alumni;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PelacakanController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    // Total alumni
    $total = Alumni::count();

    // Status pelacakan
    $belum = Alumni::whereRaw("LOWER(status_pelacakan) = 'belum dilacak'")->count();

    $teridentifikasi = Alumni::whereRaw("LOWER(status_pelacakan) = 'teridentifikasi'")->count();

    $verifikasi = Alumni::whereRaw("LOWER(status_pelacakan) = 'perlu verifikasi'")->count();

    $tidak_ditemukan = Alumni::whereRaw("LOWER(status_pelacakan) = 'tidak ditemukan'")->count();


    // Statistik per prodi
    $prodi = Alumni::select('prodi', DB::raw('count(*) as total'))
        ->whereRaw("LOWER(status_pelacakan) != 'tidak ditemukan'")
        ->groupBy('prodi')
        ->get();


    // Aktivitas terbaru
    $recent = Alumni::orderBy('updated_at','desc')
        ->limit(5)
        ->get();


    return view('dashboard', compact(
        'total',
        'belum',
        'teridentifikasi',
        'verifikasi',
        'tidak_ditemukan',
        'prodi',
        'recent'
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
| Halaman Pelacakan Alumni
|--------------------------------------------------------------------------
*/

Route::get('/pelacakan', function(){

    $alumni = Alumni::all();

    return view('pelacakan.index', compact('alumni'));

});


/*
|--------------------------------------------------------------------------
| Proses Pelacakan
|--------------------------------------------------------------------------
*/

Route::get('/lacak/{id}', [PelacakanController::class, 'lacak'])
    ->name('alumni.lacak');


/*
|--------------------------------------------------------------------------
| Halaman Statistik
|--------------------------------------------------------------------------
*/

Route::get('/statistik', function(){

    $belum = Alumni::where('status_pelacakan','Belum Dilacak')->count();
    $teridentifikasi = Alumni::where('status_pelacakan','Teridentifikasi')->count();
    $verifikasi = Alumni::where('status_pelacakan','Perlu Verifikasi')->count();
    $tidak_ditemukan = Alumni::where('status_pelacakan','Tidak Ditemukan')->count();

    $tahun = Alumni::select('tahun_lulus', DB::raw('count(*) as total'))
        ->groupBy('tahun_lulus')
        ->get();

    return view('statistik.index', compact(
        'belum',
        'teridentifikasi',
        'verifikasi',
        'tidak_ditemukan',
        'tahun'
    ));

});