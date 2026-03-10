<?php

use Illuminate\Support\Facades\Route;
use App\Models\Alumni;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PelacakanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    $total = Alumni::count();

    $belum = Alumni::where('status_pelacakan','Belum Dilacak')->count();

    $teridentifikasi = Alumni::where('status_pelacakan','Teridentifikasi')->count();

    $verifikasi = Alumni::where('status_pelacakan','Perlu Verifikasi')->count();

    $prodi = Alumni::selectRaw('prodi, count(*) as total')
            ->groupBy('prodi')
            ->get();

    return view('dashboard', compact(
        'total',
        'belum',
        'teridentifikasi',
        'verifikasi',
        'prodi'
    ));

});


/*
|--------------------------------------------------------------------------
| Route CRUD Alumni
|--------------------------------------------------------------------------
*/

Route::resource('/alumni', AlumniController::class);


/*
|--------------------------------------------------------------------------
| Route Pelacakan Alumni
|--------------------------------------------------------------------------
*/

Route::get('/lacak/{id}', [PelacakanController::class,'lacak'])->name('alumni.lacak');