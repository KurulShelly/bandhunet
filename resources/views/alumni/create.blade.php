@extends('layouts.app')

@section('content')

<h2>Tambah Alumni</h2>

<form method="POST" action="/alumni">
@csrf

<input type="text" name="nama" placeholder="Nama"><br>
<input type="text" name="prodi" placeholder="Prodi"><br>
<input type="number" name="tahun_lulus" placeholder="Tahun Lulus"><br>

<button type="submit">Simpan</button>

</form>

@endsection