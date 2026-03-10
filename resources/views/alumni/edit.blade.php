@extends('layouts.app')

@section('content')

<h2>Edit Alumni</h2>

<form method="POST" action="/alumni/{{$alumni->id}}">
@csrf
@method('PUT')

<input type="text" name="nama" value="{{$alumni->nama}}"><br><br>

<input type="text" name="prodi" value="{{$alumni->prodi}}"><br><br>

<input type="number" name="tahun_lulus" value="{{$alumni->tahun_lulus}}"><br><br>

<button type="submit">Update</button>

</form>

@endsection