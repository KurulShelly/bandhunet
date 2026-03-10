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
<th>Status Pelacakan</th>
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

<span style="
background:#dc3545;
color:white;
padding:5px 10px;
border-radius:20px;
font-size:12px;
">
Belum Dilacak
</span>

@elseif($a->status_pelacakan == "Teridentifikasi")

<span style="
background:#28a745;
color:white;
padding:5px 10px;
border-radius:20px;
font-size:12px;
">
Teridentifikasi
</span>

@else

<span style="
background:#ffc107;
color:black;
padding:5px 10px;
border-radius:20px;
font-size:12px;
">
Perlu Verifikasi
</span>

@endif

</td>

<td>

<a href="/alumni/{{$a->id}}/edit">
<button style="
background:#1E293B;
color:white;
border:none;
padding:5px 10px;
border-radius:4px;">
Edit
</button>
</a>

<form action="/alumni/{{$a->id}}" method="POST" style="display:inline;">
@csrf
@method('DELETE')

<button type="submit"
style="
background:red;
color:white;
border:none;
padding:5px 10px;
border-radius:4px;">
Hapus
</button>

</form>

<a href="/lacak/{{$a->id}}">
<button style="
background:#D4AF37;
color:white;
border:none;
padding:5px 10px;
border-radius:4px;">
Lacak
</button>
</a>

</td>

</tr>

@endforeach

</table>

@endsection