<!DOCTYPE html>
<html>
<head>
    <title>BandhuNet</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#F8FAFC;
}

.sidebar{
    width:220px;
    height:100vh;
    background:#1E293B;
    color:white;
    position:fixed;
}

.sidebar h2{
    text-align:center;
    padding:20px;
    color:#D4AF37;
}

.sidebar a{
    display:block;
    padding:15px;
    color:white;
    text-decoration:none;
}

.sidebar a:hover{
    background:#334155;
}

.main{
    margin-left:220px;
}

.navbar{
    background:white;
    padding:15px;
    border-bottom:1px solid #ddd;
}

.content{
    padding:20px;
}

.card{
    background:white;
    padding:20px;
    margin:10px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.cards{
    display:flex;
}

</style>

</head>

<body>

<div class="sidebar">

<h2>BandhuNet</h2>

<a href="/">Dashboard</a>

<a href="/alumni">Data Alumni</a>

<a href="/pelacakan">Pelacakan Alumni</a>

<a href="/statistik">Statistik</a>

</div>


<div class="main">

<div class="navbar">
Selamat datang di Sistem Pelacakan Alumni
</div>

<div class="content">

@yield('content')

</div>

</div>

</body>
</html>