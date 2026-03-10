<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPelacakan extends Model
{

protected $table = 'riwayat_pelacakan';

protected $fillable = [

    'alumni_id',
    'sumber',
    'jabatan',
    'instansi',
    'confidence_score'

];

}