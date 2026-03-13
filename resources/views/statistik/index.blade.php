@extends('layouts.app')

@section('content')

<style>

.stat-container{
display:grid;
grid-template-columns: repeat(auto-fit,minmax(180px,1fr));
gap:20px;
margin-bottom:30px;
}

.stat-card{
padding:20px;
border-radius:10px;
color:white;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
text-align:center;
}

.stat-number{
font-size:28px;
font-weight:bold;
margin-top:10px;
}

.card-belum{background:#dc3545;}
.card-teridentifikasi{background:#28a745;}
.card-verifikasi{background:#ffc107;color:black;}
.card-tidak{background:#6c757d;}

.chart-box{
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 5px 15px rgba(0,0,0,0.08);
width:100%;
max-width:700px;
margin:auto;
}

</style>


<h2 style="margin-bottom:5px;">Statistik Alumni</h2>
<p style="color:#666;margin-bottom:25px;">
Ringkasan status pelacakan alumni pada sistem BandhuNet
</p>


<!-- Card Statistik -->

<div class="stat-container">

<div class="stat-card card-belum">
<div>Belum Dilacak</div>
<div class="stat-number">{{ $belum ?? 0 }}</div>
</div>

<div class="stat-card card-teridentifikasi">
<div>Teridentifikasi</div>
<div class="stat-number">{{ $teridentifikasi ?? 0 }}</div>
</div>

<div class="stat-card card-verifikasi">
<div>Perlu Verifikasi</div>
<div class="stat-number">{{ $verifikasi ?? 0 }}</div>
</div>

<div class="stat-card card-tidak">
<div>Tidak Ditemukan</div>
<div class="stat-number">{{ $tidak_ditemukan ?? 0 }}</div>
</div>

</div>


<!-- Grafik -->

<div class="chart-box">

<h3 style="text-align:center;margin-bottom:20px;">
Distribusi Status Pelacakan Alumni
</h3>

<canvas id="chart"></canvas>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('chart');

const chart = new Chart(ctx, {

type: 'doughnut',

data: {
labels: [
'Belum Dilacak',
'Teridentifikasi',
'Perlu Verifikasi',
'Tidak Ditemukan'
],

datasets: [{
label: 'Jumlah Alumni',

data: [
{{ $belum ?? 0 }},
{{ $teridentifikasi ?? 0 }},
{{ $verifikasi ?? 0 }},
{{ $tidak_ditemukan ?? 0 }}
],

backgroundColor: [
'#dc3545',
'#28a745',
'#ffc107',
'#6c757d'
],

borderWidth: 1
}]
},

options: {

responsive: true,

plugins: {
legend: {
position: 'bottom'
}
}

}

});

</script>

@endsection