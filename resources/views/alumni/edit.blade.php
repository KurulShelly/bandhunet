@extends('layouts.app')

@section('content')

<h2>Edit Data Alumni</h2>

<br>

<form method="POST" action="/alumni/{{$alumni->id}}">
@csrf
@method('PUT')

<label>Nama Alumni</label><br>
<input type="text" name="nama" value="{{$alumni->nama}}" required>
<br><br>

<label>Program Studi</label><br>
<input type="text" name="prodi" value="{{$alumni->prodi}}" required>
<br><br>

<label>Tahun Lulus</label><br>
<input type="number" name="tahun_lulus" value="{{$alumni->tahun_lulus}}" required>
<br><br>

<label>Kota</label><br>
<input type="text" name="kota" value="{{$alumni->kota}}">
<br><br>

<label>Pekerjaan</label><br>
<input type="text" name="pekerjaan" value="{{$alumni->pekerjaan}}">
<br><br>

<label>Instansi</label><br>
<input type="text" name="instansi" value="{{$alumni->instansi}}">
<br><br>

<button type="submit"
style="
background:#1E293B;
color:white;
padding:10px 15px;
border:none;
border-radius:5px;
">
Update Data
</button>

<a href="/alumni">
<button type="button"
style="
background:#6c757d;
color:white;
padding:10px 15px;
border:none;
border-radius:5px;
">
Batal
</button>
</a>

</form>

@endsection