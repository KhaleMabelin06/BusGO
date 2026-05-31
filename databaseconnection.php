<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "db_2ita_cruzk_carnate_mabelin_busreservationsystem";
$port = 3318;

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>