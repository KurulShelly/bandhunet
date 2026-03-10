<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    protected $table = 'alumni';

    protected $fillable = [
        'nama',
        'prodi',
        'tahun_lulus',
        'kota',
        'pekerjaan',
        'instansi',
        'status_pelacakan'
    ];
}
