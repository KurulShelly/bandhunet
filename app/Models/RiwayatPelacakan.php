<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPelacakan extends Model
{
    protected $table = 'riwayat_pelacakan';

    protected $fillable = [
        'alumni_id',
        'query',
        'sumber',
        'jabatan',
        'instansi',
        'lokasi',
        'bidang',
        'tahun',
        'link',
        'confidence_score'
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}