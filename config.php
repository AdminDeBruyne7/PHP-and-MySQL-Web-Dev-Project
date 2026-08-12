<?php
// Database connection settings
// Default XAMPP credentials — change if your setup differs
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "movie_ticket_system";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
