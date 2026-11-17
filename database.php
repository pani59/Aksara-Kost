<?php

$host = "mysql.railway.internal";
$user = "root";
$password = "tpSdZFVSicuzmVHXjtkVGxjVkJqZAoOT";
$database = "railway";
$port = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


