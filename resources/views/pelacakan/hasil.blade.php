@extends('layouts.app')

@section('content')

<h2>Hasil Pelacakan Alumni</h2>

<div style="margin-bottom:20px; padding:15px; border:1px solid #ddd; border-radius:8px;">
    
    <h3>Informasi Alumni</h3>

    <p><b>Nama :</b> {{ $alumni->nama }}</p>
    <p><b>Prodi :</b> {{ $alumni->prodi }}</p>
    <p><b>Tahun Lulus :</b> {{ $alumni->tahun_lulus }}</p>
    <p><b>Status :</b> 
        <span style="padding:5px 10px; background:#eee; border-radius:5px;">
            {{ $alumni->status_pelacakan }}
        </span>
    </p>

</div>

<div style="margin-bottom:20px; padding:15px; border:1px solid #ddd; border-radius:8px;">

<h3>Query Pencarian Sistem</h3>

<ul>
<li>{{ $query1 }}</li>
<li>{{ $query2 }}</li>
<li>{{ $query3 }}</li>
<li>{{ $query4 }}</li>
</ul>

</div>

<div style="padding:15px; border:1px solid #ddd; border-radius:8px;">

<h3>Hasil Pelacakan</h3>

@if(count($hasil) == 0)

<p style="color:red;">Data alumni tidak ditemukan di sumber publik.</p>

@else

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<thead style="background:#1E293B; color:white;">
<tr>
<th>Sumber</th>
<th>Jabatan</th>
<th>Instansi</th>
<th>Lokasi</th>
<th>Bidang</th>
<th>Tahun</th>
<th>Confidence</th>
<th>Link</th>
</tr>
</thead>

<tbody>

@foreach($hasil as $h)

<tr>
<td>{{ $h['sumber'] }}</td>
<td>{{ $h['jabatan'] }}</td>
<td>{{ $h['instansi'] }}</td>
<td>{{ $h['lokasi'] }}</td>
<td>{{ $h['bidang'] }}</td>
<td>{{ $h['tahun'] }}</td>
<td>

@if($h['confidence'] >= 80)

<span style="color:green; font-weight:bold;">
{{ $h['confidence'] }}%
</span>

@elseif($h['confidence'] >= 50)

<span style="color:orange; font-weight:bold;">
{{ $h['confidence'] }}%
</span>

@else

<span style="color:red; font-weight:bold;">
{{ $h['confidence'] }}%
</span>

@endif

</td>

<td>
<a href="{{ $h['link'] }}" target="_blank">Lihat</a>
</td>

</tr>

@endforeach

</tbody>

</table>

@endif

</div>

<br>

<a href="/alumni">
<button>Kembali ke Data Alumni</button>
</a>

@endsection