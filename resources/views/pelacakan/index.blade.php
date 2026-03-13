@extends('layouts.app')

@section('content')

<style>

.container{
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:#1E293B;
    color:white;
    padding:12px;
    text-align:center;
}

.table td{
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:center;
}

.table tr:hover{
    background:#f5f5f5;
}

.btn-lacak{
    background:#D4AF37;
    color:white;
    border:none;
    padding:6px 14px;
    border-radius:6px;
    cursor:pointer;
    transition:0.3s;
}

.btn-lacak:hover{
    background:#b9972f;
}

.badge{
    padding:4px 10px;
    border-radius:12px;
    color:white;
    font-size:12px;
}

.belum{ background:#dc3545; }
.identifikasi{ background:#28a745; }
.verifikasi{ background:#ffc107; color:black; }
.tidak{ background:#6c757d; }

.title{
    margin-bottom:20px;
}

</style>


<div class="container">

<h2 class="title">Pelacakan Alumni</h2>

<table class="table">

<tr>
<th>No</th>
<th>Nama</th>
<th>Prodi</th>
<th>Tahun Lulus</th>
<th>Status</th>
<th>Aksi</th>
</tr>

@php $no = 1; @endphp

@if($alumni->count() > 0)

@foreach($alumni as $a)

<tr>

<td>{{ $no++ }}</td>

<td>{{ $a->nama }}</td>

<td>{{ $a->prodi }}</td>

<td>{{ $a->tahun_lulus }}</td>

<td>

@if($a->status_pelacakan == 'Belum Dilacak')
<span class="badge belum">Belum Dilacak</span>

@elseif($a->status_pelacakan == 'Teridentifikasi')
<span class="badge identifikasi">Teridentifikasi</span>

@elseif($a->status_pelacakan == 'Perlu Verifikasi')
<span class="badge verifikasi">Perlu Verifikasi</span>

@else
<span class="badge tidak">Tidak Ditemukan</span>
@endif

</td>

<td>

<a href="/lacak/{{$a->id}}">
<button class="btn-lacak">
🔎 Lacak
</button>
</a>

</td>

</tr>

@endforeach

@else

<tr>
<td colspan="6">
Data alumni belum tersedia
</td>
</tr>

@endif

</table>

</div>

@endsection