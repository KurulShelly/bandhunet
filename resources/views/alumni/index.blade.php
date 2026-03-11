@extends('layouts.app')

@section('content')

<h2>Data Alumni</h2>

<a href="/alumni/create">
<button style="background:#D4AF37;color:white;padding:10px;border:none;border-radius:5px;">
Tambah Alumni
</button>
</a>

<br><br>

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<tr style="background:#1E293B;color:white;">
<th>No</th>
<th>Nama</th>
<th>Prodi</th>
<th>Tahun Lulus</th>
<th>Status</th>
<th>Pencarian Akademik</th>
<th>Pencarian Profesional</th>
<th>Verifikasi Web</th>
<th>Aksi</th>
</tr>

@php $no = 1; @endphp

@foreach($alumni as $a)

<tr>

<td>{{ $no++ }}</td>

<td>{{ $a->nama }}</td>

<td>{{ $a->prodi }}</td>

<td>{{ $a->tahun_lulus }}</td>

<td align="center">

@if($a->status_pelacakan == "Belum Dilacak")

<span style="background:#dc3545;color:white;padding:5px 10px;border-radius:20px;font-size:12px;">
Belum Dilacak
</span>

@elseif($a->status_pelacakan == "Teridentifikasi")

<span style="background:#28a745;color:white;padding:5px 10px;border-radius:20px;font-size:12px;">
Terdeteksi
</span>

@else

<span style="background:#ffc107;color:black;padding:5px 10px;border-radius:20px;font-size:12px;">
Perlu Verifikasi
</span>

@endif

</td>

<!-- Pencarian Akademik -->
<td align="center">

<a target="_blank"
href="https://scholar.google.com/scholar?q={{ urlencode($a->nama.' Universitas Muhammadiyah Malang') }}">

<button style="background:#2563eb;color:white;border:none;padding:5px 10px;border-radius:4px;">
Scholar
</button>

</a>

<a target="_blank"
href="https://orcid.org/orcid-search/search?searchQuery={{ urlencode($a->nama) }}">

<button style="background:#0ea5e9;color:white;border:none;padding:5px 10px;border-radius:4px;">
ORCID
</button>

</a>

</td>


<!-- Pencarian Profesional -->
<td align="center">

<a target="_blank"
href="https://www.linkedin.com/search/results/all/?keywords={{ urlencode($a->nama.' Universitas Muhammadiyah Malang') }}">

<button style="background:#0a66c2;color:white;border:none;padding:5px 10px;border-radius:4px;">
LinkedIn
</button>

</a>

</td>


<!-- Verifikasi Web -->
<td align="center">

<a target="_blank"
href="https://www.google.com/search?q={{ urlencode($a->nama.' Universitas Muhammadiyah Malang') }}">

<button style="background:#16a34a;color:white;border:none;padding:5px 10px;border-radius:4px;">
Google
</button>

</a>

</td>


<td>

<a href="/alumni/{{$a->id}}/edit">
<button style="background:#1E293B;color:white;border:none;padding:5px 10px;border-radius:4px;">
Edit
</button>
</a>

<form action="/alumni/{{$a->id}}" method="POST" style="display:inline;">
@csrf
@method('DELETE')

<button type="submit"
style="background:red;color:white;border:none;padding:5px 10px;border-radius:4px;">
Hapus
</button>

</form>

<a href="/lacak/{{$a->id}}">
<button style="background:#D4AF37;color:white;border:none;padding:5px 10px;border-radius:4px;">
Lacak
</button>
</a>

</td>

</tr>

@endforeach

</table>

@endsection