@extends('layouts.app')

@section('content')

<h2 style="margin-bottom:20px;">Hasil Pelacakan Alumni</h2>

<!-- DATA ALUMNI -->
<div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;">

<h3>Profil Alumni</h3>

<p><b>Nama :</b> {{ $alumni->nama }}</p>
<p><b>Program Studi :</b> {{ $alumni->prodi }}</p>
<p><b>Tahun Lulus :</b> {{ $alumni->tahun_lulus }}</p>

<p>
<b>Status Pelacakan :</b>

<span style="background:#16a34a;color:white;padding:5px 10px;border-radius:5px;">
{{ $alumni->status_pelacakan }}
</span>

</p>

</div>


<!-- QUERY PENCARIAN -->

<div style="background:#eef2ff;padding:15px;border-radius:8px;margin-bottom:20px;">

<h3>Query Pencarian Sistem</h3>

<ul>

<li>{{ $query1 }}</li>
<li>{{ $query2 }}</li>
<li>{{ $query3 }}</li>

</ul>

</div>


<!-- HASIL PELACAKAN -->

<h3>Kandidat Hasil Pelacakan</h3>

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<tr style="background:#1E293B;color:white;">

<th>Nama</th>
<th>Jabatan</th>
<th>Instansi</th>
<th>Sumber</th>
<th>Status</th>
<th>Confidence</th>

</tr>

@foreach($hasil as $h)

<tr>

<td>{{ $h['nama'] }}</td>

<td>{{ $h['jabatan'] }}</td>

<td>{{ $h['instansi'] }}</td>

<td>{{ $h['sumber'] }}</td>

<td>

@if($h['status'] == "Kemungkinan Kuat")

<span style="color:green;font-weight:bold;">
{{ $h['status'] }}
</span>

@else

<span style="color:orange;font-weight:bold;">
{{ $h['status'] }}
</span>

@endif

</td>

<td>

<span style="background:#D4AF37;color:white;padding:5px 10px;border-radius:5px;">
{{ $h['confidence'] }}%
</span>

</td>

</tr>

@endforeach

</table>


<br>

<a href="/alumni">

<button style="background:#1E293B;color:white;padding:10px 15px;border:none;border-radius:5px;">
Kembali ke Data Alumni
</button>

</a>

@endsection