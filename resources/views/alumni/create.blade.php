@extends('layouts.app')

@section('content')

<style>
.form-container {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.form-container h2 {
    margin-bottom: 20px;
    color: #1E293B;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #1E293B;
}

input, select, textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 14px;
    transition: all 0.2s;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #D4AF37;
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

button.submit-btn {
    background: #D4AF37;
    color: white;
}

button.submit-btn:hover {
    background: #c29b2f;
}

button.cancel-btn {
    background: #6c757d;
    color: white;
    margin-left: 10px;
}

button.cancel-btn:hover {
    background: #5a6268;
}

.alert-error {
    background:#fee2e2;
    color:#b91c1c;
    padding:12px 15px;
    border-radius:6px;
    margin-bottom:20px;
    border:1px solid #fca5a5;
}

@media(max-width:600px){
    .form-container{
        padding:20px;
    }
    button{
        width:100%;
        margin-top:10px;
    }
}
</style>

<div class="form-container">

<h2>Tambah Data Alumni</h2>

@if ($errors->any())
<div class="alert-error">
    <ul style="margin:0;padding-left:20px;">
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif

<form method="POST" action="/alumni">
@csrf

<div class="form-group">
<label>Nama Alumni</label>
<input type="text" name="nama" placeholder="Masukkan nama alumni" required>
</div>

<div class="form-group">
<label>Program Studi</label>
<input type="text" name="prodi" placeholder="Contoh: Informatika" required>
</div>

<div class="form-group">
<label>Tahun Lulus</label>
<input type="number" name="tahun_lulus" placeholder="Contoh: 2023" required>
</div>

<div class="form-group">
<label>Kota</label>
<input type="text" name="kota" placeholder="Contoh: Malang">
</div>

<div class="form-group">
<label>Pekerjaan</label>
<input type="text" name="pekerjaan" placeholder="Contoh: Software Engineer">
</div>

<div class="form-group">
<label>Instansi</label>
<input type="text" name="instansi" placeholder="Contoh: Gojek / Tokopedia">
</div>

<div style="margin-top:10px;">
<button type="submit" class="submit-btn">Simpan Data</button>
<a href="/alumni">
<button type="button" class="cancel-btn">Batal</button>
</a>
</div>

</form>
</div>

@endsection