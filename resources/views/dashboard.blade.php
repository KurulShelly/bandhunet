@extends('layouts.app')

@section('content')

<style>

.dashboard-grid{
display:grid;
grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
gap:20px;
margin-bottom:30px;
}

.stat-card{
padding:20px;
border-radius:10px;
color:white;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
transition:0.3s;
}

.stat-card:hover{
transform:translateY(-5px);
}

.card-total{background:#1E293B;}
.card-belum{background:#dc3545;}
.card-teridentifikasi{background:#28a745;}
.card-verifikasi{background:#ffc107;color:black;}
.card-tidak{background:#6c757d;}

.chart-container{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.chart-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:30px;
}

</style>

<h2>Dashboard BandhuNet</h2>

<div class="dashboard-grid">

<div class="stat-card card-total">
<h3>Total Alumni</h3>
<p style="font-size:28px;">{{ $total ?? 0 }}</p>
</div>

<div class="stat-card card-belum">
<h3>Belum Dilacak</h3>
<p style="font-size:28px;">{{ $belum ?? 0 }}</p>
</div>

<div class="stat-card card-teridentifikasi">
<h3>Teridentifikasi</h3>
<p style="font-size:28px;">{{ $teridentifikasi ?? 0 }}</p>
</div>

<div class="stat-card card-verifikasi">
<h3>Perlu Verifikasi</h3>
<p style="font-size:28px;">{{ $verifikasi ?? 0 }}</p>
</div>

<div class="stat-card card-tidak">
<h3>Tidak Ditemukan</h3>
<p style="font-size:28px;">{{ $tidak_ditemukan ?? 0 }}</p>
</div>

</div>


<div class="chart-grid">

<div class="chart-container">
<h3>Status Pelacakan Alumni</h3>
<canvas id="statusChart"></canvas>
</div>

<div class="chart-container">
<h3>Alumni per Prodi</h3>
<canvas id="prodiChart"></canvas>
</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

// Pie Chart Status
const statusChart = new Chart(
document.getElementById('statusChart'),
{
type:'doughnut',
data:{
labels:[
'Belum Dilacak',
'Teridentifikasi',
'Perlu Verifikasi',
'Tidak Ditemukan'
],
datasets:[{
data:[
{{ $belum ?? 0 }},
{{ $teridentifikasi ?? 0 }},
{{ $verifikasi ?? 0 }},
{{ $tidak_ditemukan ?? 0 }}
],
backgroundColor:[
'#dc3545',
'#28a745',
'#ffc107',
'#6c757d'
]
}]
}
}
);


// Bar Chart Prodi
const prodiChart = new Chart(
document.getElementById('prodiChart'),
{
type:'bar',
data:{
labels:[
@foreach(($prodi ?? []) as $p)
'{{ $p->prodi }}',
@endforeach
],
datasets:[{
label:'Jumlah Alumni',
data:[
@foreach(($prodi ?? []) as $p)
{{ $p->total }},
@endforeach
]
}]
},
options:{
responsive:true,
plugins:{
legend:{display:false}
}
}
}
);

</script>

@endsection