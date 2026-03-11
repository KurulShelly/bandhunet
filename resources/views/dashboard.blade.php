@extends('layouts.app')

@section('content')

<h2>Dashboard BandhuNet</h2>

    <div style="display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap;">

        <div class="card">
        <h3>Total Alumni</h3>
        <p style="font-size:22px;">{{ $total ?? 0 }}</p>
    </div>

    <div class="card">
        <h3>Belum Dilacak</h3>
        <p style="font-size:22px;">{{ $belum ?? 0 }}</p>
    </div>

    <div class="card">
        <h3>Teridentifikasi</h3>
        <p style="font-size:22px;">{{ $teridentifikasi ?? 0 }}</p>
    </div>

    <div class="card">
        <h3>Perlu Verifikasi</h3>
        <p style="font-size:22px;">{{ $verifikasi ?? 0 }}</p>
    </div>

    <div class="card">
        <h3>Tidak Ditemukan</h3>
        <p style="font-size:22px;">{{ $tidak ?? 0 }}</p>
    </div>

</div>


<div style="display:flex; gap:30px; flex-wrap:wrap;">

<div style="width:48%;">
    <h3>Status Pelacakan Alumni</h3>
    <canvas id="statusChart"></canvas>
</div>

<div style="width:48%;">
    <h3>Alumni per Prodi</h3>
    <canvas id="prodiChart"></canvas>
</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const statusChart = new Chart(
document.getElementById('statusChart'),
{
    type:'pie',
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
        {{ $tidak ?? 0 }}
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
    ],
        backgroundColor:'#1E293B'
         }]
    }
}
);

</script>

@endsection