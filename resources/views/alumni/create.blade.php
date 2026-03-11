@extends('layouts.app')

@section('content')

<h2>Tambah Data Alumni</h2>

<br>

@if ($errors->any())
<div style="color:red;margin-bottom:15px;">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif


<form method="POST" action="/alumni">
@csrf

<label>Nama Alumni</label><br>
<input type="text" name="nama" placeholder="Masukkan nama alumni" required>
<br><br>

<label>Program Studi</label><br>
<input type="text" name="prodi" placeholder="Contoh: Informatika" required>
<br><br>

<label>Tahun Lulus</label><br>
<input type="number" name="tahun_lulus" placeholder="Contoh: 2023" required>
<br><br>

<label>Kota</label><br>
<input type="text" name="kota" placeholder="Contoh: Malang">
<br><br>

<label>Pekerjaan</label><br>
<input type="text" name="pekerjaan" placeholder="Contoh: Software Engineer">
<br><br>

<label>Instansi</label><br>
<input type="text" name="instansi" placeholder="Contoh: Gojek / Tokopedia">
<br><br>

<button type="submit"
style="
background:#D4AF37;
color:white;
padding:10px 15px;
border:none;
border-radius:5px;
">
Simpan Data
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