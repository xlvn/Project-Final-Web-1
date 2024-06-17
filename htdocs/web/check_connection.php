<!-- <?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "healt_plus_data";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi berhasil";
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?> -->
