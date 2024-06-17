<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "healt_plus_data";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['full_name'])) {
    header("Location: ../pages/sign-in-pasien.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$id_pasien = $_SESSION['id_pasien'];

$query = "SELECT pr.tgl_periksa, pr.biaya_periksa, dp.no_antrian, dp.keluhan
FROM periksa pr 
INNER JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id 
INNER JOIN pasien p ON dp.id_pasien = p.id 
WHERE p.nama = '$full_name' 
ORDER BY pr.id DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

$sql_antrian = "SELECT MAX(no_antrian) AS max_antrian FROM daftar_poli";
$result_antrian = $conn->query($sql_antrian);
$next_antrian = 1;
if ($result_antrian->num_rows > 0) {
    $row_antrian = $result_antrian->fetch_assoc();
    $next_antrian = $row_antrian['max_antrian'] + 1;
}

$sql_poli = "SELECT id, nama_poli FROM poli";
$result_poli = $conn->query($sql_poli);

$sql_jadwal = "SELECT id, hari, jam_mulai, jam_selesai FROM jadwal_periksa";
$result_jadwal = $conn->query($sql_jadwal);

// Query untuk menghitung total biaya periksa
$query_total_biaya_periksa = "
    SELECT SUM(pr.biaya_periksa) AS total_biaya_periksa
    FROM periksa pr
    INNER JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
    WHERE dp.id_pasien = '$id_pasien'
";

$result_total_biaya_periksa = mysqli_query($conn, $query_total_biaya_periksa);

if (!$result_total_biaya_periksa) {
    die("Error: " . mysqli_error($conn));
}

$row_total_biaya_periksa = mysqli_fetch_assoc($result_total_biaya_periksa);
$total_biaya_periksa = $row_total_biaya_periksa['total_biaya_periksa'];

// Query untuk menghitung total biaya obat
$query_total_biaya_obat = "
    SELECT SUM(o.harga) AS total_biaya_obat
    FROM periksa pr
    INNER JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
    INNER JOIN detail_periksa dpk ON pr.id = dpk.id_periksa
    INNER JOIN obat o ON dpk.id_obat = o.id
    WHERE dp.id_pasien = '$id_pasien'
";

$result_total_biaya_obat = mysqli_query($conn, $query_total_biaya_obat);

if (!$result_total_biaya_obat) {
    die("Error: " . mysqli_error($conn));
}

$row_total_biaya_obat = mysqli_fetch_assoc($result_total_biaya_obat);
$total_biaya_obat = $row_total_biaya_obat['total_biaya_obat'];

// Hitung total biaya keseluruhan
$total_biaya = $total_biaya_periksa + $total_biaya_obat;

// Query untuk menampilkan riwayat layanan kesehatan dengan informasi obat
$query_history = "
    SELECT pr.id, pr.tgl_periksa, pr.catatan, pr.biaya_periksa, 
           o.nama_obat, o.harga AS harga_obat
    FROM periksa pr
    INNER JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
    INNER JOIN detail_periksa dpk ON pr.id = dpk.id_periksa
    INNER JOIN obat o ON dpk.id_obat = o.id
    WHERE dp.id_pasien = '$id_pasien'
    ORDER BY pr.id DESC
";
$result_history = mysqli_query($conn, $query_history);

/// Query untuk menampilkan data dari tabel daftar_poli dan jadwal_periksa
$query = "
SELECT dp.id, dp.id_pasien, dp.id_jadwal, dp.keluhan, dp.no_antrian, jp.hari, jp.jam_mulai, jp.jam_selesai
FROM daftar_poli dp
JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
WHERE dp.id_pasien = '$id_pasien'
ORDER BY dp.no_antrian ASC
";
$result = mysqli_query($conn, $query);

if (!$result) {
die("Error: " . mysqli_error($conn));
}

$sql_antrian = "SELECT MAX(no_antrian) AS max_antrian FROM daftar_poli";
$result_antrian = $conn->query($sql_antrian);
$next_antrian = 1;
if ($result_antrian->num_rows > 0) {
$row_antrian = $result_antrian->fetch_assoc();
$next_antrian = $row_antrian['max_antrian'] + 1;
}

$sql_poli = "SELECT id, nama_poli FROM poli";
$result_poli = $conn->query($sql_poli);

$sql_jadwal = "SELECT id, hari, jam_mulai, jam_selesai FROM jadwal_periksa";
$result_jadwal = $conn->query($sql_jadwal);

$status_message = isset($_SESSION['status_message']) ? $_SESSION['status_message'] : "";
unset($_SESSION['status_message']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$id_antrian = $_POST['id'];
$jadwal = $_POST['jadwal'];
$keluhan = $_POST['keluhan'];

$sql = "INSERT INTO daftar_poli (no_antrian, id_jadwal, id_pasien, keluhan) 
        VALUES ('$id_antrian', '$jadwal', '$id_pasien', '$keluhan')";

if ($conn->query($sql) === TRUE) {
    $_SESSION['status_message'] = "Jadwal berhasil ditambahkan!";
} else {
    $_SESSION['status_message'] = "Error: " . $sql . "<br>" . $conn->error;
}

header("Location: dashboard-pasien.php");
exit();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
      Health Plus by  Health Plus Team
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />
  <!-- Nepcha Analytics (nepcha.com) -->
  <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
  <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="  " target="_blank">
        <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">  Health Plus</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link  active" href="../pages/dashboard-pasien.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <title>credit-card</title>
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                    <g transform="translate(1716.000000, 291.000000)">
                      <g transform="translate(453.000000, 454.000000)">
                        <path class="color-background opacity-6" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z"></path>
                        <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                      </g>
                    </g>
                  </g>
                </g>
              </svg>
            </div>
            <span class="nav-link-text ms-1">Pemeriksaan</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account pages</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link  " href="">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <svg width="12px" height="12px" viewBox="0 0 46 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <title>customer-support</title>
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g transform="translate(-1717.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                    <g transform="translate(1716.000000, 291.000000)">
                      <g transform="translate(1.000000, 0.000000)">
                        <path class="color-background opacity-6" d="M45,0 L26,0 C25.447,0 25,0.447 25,1 L25,20 C25,20.379 25.214,20.725 25.553,20.895 C25.694,20.965 25.848,21 26,21 C26.212,21 26.424,20.933 26.6,20.8 L34.333,15 L45,15 C45.553,15 46,14.553 46,14 L46,1 C46,0.447 45.553,0 45,0 Z"></path>
                        <path class="color-background" d="M22.883,32.86 C20.761,32.012 17.324,31 13,31 C8.676,31 5.239,32.012 3.116,32.86 C1.224,33.619 0,35.438 0,37.494 L0,41 C0,41.553 0.447,42 1,42 L25,42 C25.553,42 26,41.553 26,41 L26,37.494 C26,35.438 24.776,33.619 22.883,32.86 Z"></path>
                        <path class="color-background" d="M13,28 C17.432,28 21,22.529 21,18 C21,13.589 17.411,10 13,10 C8.589,10 5,13.589 5,18 C5,22.529 8.568,28 13,28 Z"></path>
                      </g>
                    </g>
                  </g>
                </g>
              </svg>
            </div>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="sidenav-footer mx-3 ">
      <div class="card card-background shadow-none card-background-mask-secondary" id="sidenavCard">
        <div class="full-background" style="background-image: url('../assets/img/curved-images/white-curved.jpg')"></div>
        <div class="card-body text-start p-3 w-100">
          <div class="icon icon-shape icon-sm bg-white shadow text-center mb-3 d-flex align-items-center justify-content-center border-radius-md">
            <i class="ni ni-diamond text-dark text-gradient text-lg top-0" aria-hidden="true" id="sidenavCardIcon"></i>
          </div>
          <div class="docs-info">
            <h6 class="text-white up mb-0">   Butuh bantuan?</h6>
            <p class="text-xs font-weight-bold">Please check our docs</p>
            <a href="   " target="_blank" class="btn btn-white btn-sm w-100 mb-0">KONTAK DEVELOPER</a>
          </div>
        </div>
      </div>
      <a class="btn bg-gradient-primary mt-3 w-100" href="../index.php">LOGOUT</a>
    </div>
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Pemeriksaan</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Pemeriksaan</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group">
              <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
              <input type="text" class="form-control" placeholder="Type here...">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a class="btn btn-outline-primary btn-sm mb-0 me-3" target="_blank" href="">Online</a>
            </li>
            <li class="nav-item">
    <a class="nav-link me-2" href="../pages/sign-in.php">
        <i class="fa fa-user me-sm-1"></i>
        <?php
        // Cek apakah pengguna sudah login
        if (isset($_SESSION['full_name'])) {
            // Jika sudah, tampilkan nama lengkap pengguna sebagai teks "Sign In"
            echo '<span class="nav-link-text ms-1">' . $_SESSION['full_name'] . '</span>';
        } else {
            // Jika belum, tampilkan "Sign In" seperti biasa
            echo '<span class="nav-link-text ms-1">Sign In</span>';
        }
        ?>
    </a>
</li>

            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
              </a>
              <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <title>credit-card</title>
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                              <g transform="translate(1716.000000, 291.000000)">
                                <g transform="translate(453.000000, 454.000000)">
                                  <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                  <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          Payment successfully completed
                        </h6>
                        <p class="text-xs text-secondary mb-0 ">
                          <i class="fa fa-clock me-1"></i>
                          2 days
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-lg-8">
          <div class="row">
            <div class="col-xl-6 mb-xl-0 mb-4">
              <div class="card bg-transparent shadow-xl">
                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../assets/img/curved-images/curved14.jpg');">
                  <span class="mask bg-gradient-dark"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                    <i class="fas fa-wifi text-white p-2"></i>
                    <h5 class="text-white mt-4 mb-5 pb-2">4562&nbsp;&nbsp;&nbsp;1122&nbsp;&nbsp;&nbsp;4594&nbsp;&nbsp;&nbsp;7852</h5>
                    <div class="d-flex">
                      <div class="d-flex">
                        <div class="me-4">
                          <p class="text-white text-sm opacity-8 mb-0">Card Holder</p>
                          <h6 class="text-white mb-0">
                          <?php
        // Cek apakah pengguna sudah login
        if (isset($_SESSION['full_name'])) {
            // Jika sudah, tampilkan nama lengkap pengguna sebagai teks "Sign In"
            echo '<span class="nav-link-text ms-1">' . $_SESSION['full_name'] . '</span>';
        } else {
            // Jika belum, tampilkan "Sign In" seperti biasa
            echo '<span class="nav-link-text ms-1">Sign In</span>';
        }
        ?>
                          </h6>
                        </div>
                        <div>
                          <p class="text-white text-sm opacity-8 mb-0">Expires</p>
                          <h6 class="text-white mb-0">11/24</h6>
                        </div>
                      </div>
                      <div class="ms-auto w-20 d-flex align-items-end justify-content-end">
                        <img class="w-60 mt-2" src="../assets/img/logos/mastercard.png" alt="logo">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-6">
  <div class="card">
    <div class="card-header mx-4 p-3 text-center">
      <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
        <i class="fas fa-landmark opacity-10"></i>
      </div>
    </div>
    <div class="card-body pt-0 p-3 text-center">
      <h6 class="text-center mb-0">Pembayaran</h6>
      <span class="text-xs">Total Tagihan sementara</span>
      <hr class="horizontal dark my-3">
      <h5 class="mb-0">
      <?php
// Pastikan $total_biaya terdefinisi dan memiliki nilai sebelum menggunakannya
if (isset($total_biaya)) {
    echo "Rp. " . number_format($total_biaya, 0, ',', ' ');
} else {
    echo "Rp. 0"; // Atau pesan lain yang sesuai jika $total_biaya tidak terdefinisi
}
?>

      </h5>
    </div>
  </div>
</div>


            <div class="col-md-12 mb-lg-0 mb-4">
              <div class="card mt-4">
                <div class="card-header pb-0 p-3">
                  <div class="row">
                    <div class="col-6 d-flex align-items-center">
                      <h6 class="mb-0">Metode Pembayaran</h6>
                    </div>
                    <div class="col-6 text-end">
                      <a class="btn bg-gradient-dark mb-0" href="javascript:;"><i class="fas fa-plus"></i>&nbsp;&nbsp;Tambah</a>
                    </div>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="row">
                    <div class="col-md-6 mb-md-0 mb-4">
                      <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <img class="w-10 me-3 mb-0" src="../assets/img/logos/mastercard.png" alt="logo">
                        <h6 class="mb-0">****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;7852</h6>
                        <i class="fas fa-pencil-alt ms-auto text-dark cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Card"></i>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <img class="w-10 me-3 mb-0" src="../assets/img/logos/visa.png" alt="logo">
                        <h6 class="mb-0">****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;5248</h6>
                        <i class="fas fa-pencil-alt ms-auto text-dark cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Card"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          
  <div class="card h-100">
    <div class="card-header pb-0 p-3">
      <div class="row">
        <div class="col-6 d-flex align-items-center">
          <h6 class="mb-0">Jadwal Periksa</h6>
        </div>
        <div class="col-6 text-end">
          <a class="btn btn-outline-primary btn-sm mb-0 " data-bs-toggle="modal" data-bs-target="#tambahJadwalModal">Buat Jadwal</a>
        </div>

<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="tambahJadwalModal" tabindex="-1" aria-labelledby="tambahJadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahJadwalModalLabel">Tambah Jadwal Periksa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formJadwal" method="POST">
                    <div class="form-group">
                        <label for="id">Antrian :</label>
                        <input type="text" class="form-control" id="id" name="id" value="<?php echo $next_antrian; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="poli">Pilih Poli:</label>
                        <select class="form-control" id="poli" name="poli">
                            <?php
                            if ($result_poli->num_rows > 0) {
                                while($row = $result_poli->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['nama_poli'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No data</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jadwal">Pilih Jadwal:</label>
                        <select class="form-control" id="jadwal" name="jadwal">
                            <?php
                            if ($result_jadwal->num_rows > 0) {
                                while($row = $result_jadwal->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['hari'] . " " . $row['jam_mulai'] . " - " . $row['jam_selesai'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No data</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="keluhan">Keluhan:</label>
                        <textarea class="form-control" id="keluhan" name="keluhan" rows="3"></textarea>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">Buat Jadwal Baru</button>
                    </div>
                </form>
                <?php if (!empty($_SESSION['status_message'])): ?>
                    <div class="status-message">
                        <?php echo $_SESSION['status_message']; ?>
                    </div>
                    <?php unset($_SESSION['status_message']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

      </div>
    </div>
    <div class="card-body p-3 pb-0">
        <ul class="list-group">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<li class='list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg'>";
                    echo "<div class='d-flex flex-column'>";
                    
                    echo "<span class='text-xs'>Antrian nomor " . $row["no_antrian"] . "</span>";

                    echo "</div>";
                    echo "<div class='d-flex align-items-center text-sm'>";
                    echo "<p class='text-xs'>Keluhan: " . $row["keluhan"] . "</p>";
                    echo "</div>";
                    echo "</li>";
                }
            } else {
                echo "<li class='list-group-item border-0'>Tidak ada data</li>";
            }
            ?>
        </ul>
    </div>


  </div>
</div>
<div class="row">
    <div class="col-md-7 mt-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Riwayat Layanan Kesehatan</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <ul class="list-group">
                    <?php
                    $previous_id = null;
                    $details = [];

                    while ($row = mysqli_fetch_assoc($result_history)) {
                        $current_id = $row['id'];
                        $formatted_date = date("d F Y", strtotime($row["tgl_periksa"]));
                        
                        // Jika pemeriksaan berbeda, tampilkan data yang sudah dikumpulkan
                        if ($previous_id !== null && $current_id != $previous_id) {
                            echo "<li class='list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg'>";
                            echo "<div class='d-flex flex-column'>";
                            echo "<h6 class='mb-3 text-sm'>" . $details['tgl_periksa'] . "</h6>";
                            echo "<span class='mb-2 text-xs'>Catatan: <span class='text-dark font-weight-bold ms-sm-2'>" . $details['catatan'] . "</span></span>";
                            echo "<span class='mb-2 text-xs'>Biaya Periksa: <span class='text-dark ms-sm-2 font-weight-bold'>Rp. " . number_format($details['biaya_periksa'], 0, ',', '.') . "</span></span>";
                            
                            echo "<span class='mb-2 text-xs'>Obat:</span>";
                            foreach ($details['obat'] as $obat) {
                                echo "<span class='mb-2 text-xs ms-3'>Nama Obat: <span class='text-dark font-weight-bold'>" . $obat['nama_obat'] . "</span>, Harga: <span class='text-dark font-weight-bold'>Rp. " . number_format($obat['harga_obat'], 0, ',', '.') . "</span></span><br>";
                            }
                            
                            echo "</div>";
                            echo "</li>";
                            $details = [];
                        }

                        // Kumpulkan data
                        $details['tgl_periksa'] = $formatted_date;
                        $details['catatan'] = $row['catatan'];
                        $details['biaya_periksa'] = $row['biaya_periksa'];
                        $details['obat'][] = [
                            'nama_obat' => $row['nama_obat'],
                            'harga_obat' => $row['harga_obat']
                        ];
                        
                        $previous_id = $current_id;
                    }

                    // Tampilkan data terakhir
                    if (!empty($details)) {
                        echo "<li class='list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg'>";
                        echo "<div class='d-flex flex-column'>";
                        echo "<h6 class='mb-3 text-sm'>" . $details['tgl_periksa'] . "</h6>";
                        echo "<span class='mb-2 text-xs'>Catatan: <span class='text-dark font-weight-bold ms-sm-2'>" . $details['catatan'] . "</span></span>";
                        echo "<span class='mb-2 text-xs'>Biaya Periksa: <span class='text-dark ms-sm-2 font-weight-bold'>Rp. " . number_format($details['biaya_periksa'], 0, ',', '.') . "</span></span>";
                        
                        echo "<span class='mb-2 text-xs'>Obat:</span>";
                        foreach ($details['obat'] as $obat) {
                            echo "<span class='mb-2 text-xs ms-3'>Nama Obat: <span class='text-dark font-weight-bold'>" . $obat['nama_obat'] . "</span>, Harga: <span class='text-dark font-weight-bold'>Rp. " . number_format($obat['harga_obat'], 0, ',', '.') . "</span></span><br>";
                        }
                        
                        echo "</div>";
                        echo "</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-5 mt-4">
  <div class="card h-100 mb-4">
    <div class="card-body pt-4 p-3">
      <p class="text-justify">
        "Setiap hari adalah kesempatan baru untuk hidup lebih sehat. Jangan pernah menyerah dalam perjuangan untuk menjadi versi terbaik 
        dari diri Anda. Dengan kesehatan yang baik, Anda bisa mencapai lebih banyak hal dan menikmati hidup dengan lebih baik. 
        Tetaplah termotivasi dan teruslah bergerak maju."
      </p>
    </div>
  </div>
</div>




      <footer class="footer pt-3  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                Health Plus  
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                      <a href="#" class="nav-link text-muted" target="_blank"> Health Plus Team</a>
                </li>
                <li class="nav-item">
                      <a href="#/presentation" class="nav-link text-muted" target="_blank">About Us</a>
                </li>
                <li class="nav-item">
                      <a href="#/blog" class="nav-link text-muted" target="_blank">Blog</a>
                </li>
                <li class="nav-item">
                      <a href="#/license" class="nav-link pe-0 text-muted" target="_blank">License</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="fa fa-cog py-2"> </i>
    </a>
    <div class="card shadow-lg ">
      <div class="card-header pb-0 pt-3 ">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Soft UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="fa fa-close"></i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-start">
            <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-primary w-100 px-3 mb-2 active" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-primary w-100 px-3 mb-2 ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3">
          <h6 class="mb-0">Navbar Fixed</h6>
        </div>
        <div class="form-check form-switch ps-0">
          <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
        </div>
        <hr class="horizontal dark my-sm-4">
        <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/soft-ui-dashboard">Free Download</a>
        <a class="btn btn-outline-dark w-100" href="   ">View KONTAK DEVELOPER</a>
        <div class="w-100 text-center">
          <a class="github-button" href="https://github.com/creativetimofficial/soft-ui-dashboard" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/soft-ui-dashboard on GitHub">Star</a>
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="https://twitter.com/intent/tweet?text=Check%20Soft%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/soft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>