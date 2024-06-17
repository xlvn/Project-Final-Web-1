<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
<link rel="icon" type="image/png" href="./assets/img/favicon.png">

<title>Health Plus by Health Plus Team</title>

<!-- Fonts and icons -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
<link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
<link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
<link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
<link id="pagestyle" href="./assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />

<style>
    body {
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #333; /* Warna latar belakang jika gambar tidak dimuat */
        overflow: hidden;
        position: relative;
    }
    .background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('https://asset.kompas.com/crops/-NpBgtVbhgJPmpcoSwdusbM6OXA=/0x0:0x0/1200x800/data/photo/2020/12/14/5fd76b50854a0.jpg') no-repeat center center fixed;
        background-size: cover;
        z-index: 1;
    }
    .glassmorphism {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 2;
    }
    .content {
        position: relative;
        z-index: 3;
        background: rgba(255, 255, 255, 0.7); /* Lebih opak agar teks lebih mudah dibaca */
        border-radius: 15px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 90%;
        text-align: center;
    }
    .btn-custom {
        margin-top: 10px;
    }
    .role-description {
        margin-top: 10px;
    }
</style>
</head>

<body>
<div class="background"></div>
<div class="glassmorphism"></div>
<div class="content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center mb-4">
                    <div class="card-header text-white">
                        <h1>Selamat Datang di Health Plus</h1>
                    </div>
                </div>
            </div>
        </div>

  <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center mb-4">
                    <div class="card-body">
                        <button onclick="window.location.href='./pages/sign-in-dokter.php'" class="btn btn-primary btn-custom">Masuk Sebagai Dokter</button>
                        <p class="role-description">Masuk untuk mengelola data pasien dan memberikan konsultasi.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card text-center mb-4">
                    <div class="card-body">
                        <button onclick="window.location.href='./pages/sign-in-pasien.php'" class="btn btn-primary btn-custom">Masuk Sebagai Pasien</button>
                        <p class="role-description">Masuk untuk melakukan pendaftaran untuk pelayanan kesehatan.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="text-center">
                    <p></p> <!-- Paragraf kosong untuk ruang kosong atas -->
                    <a href="./pages/sign-in-admin.php" class="admin-link">Login sebagai Admin</a>
                    <p></p> <!-- Paragraf kosong untuk ruang kosong bawah -->
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
