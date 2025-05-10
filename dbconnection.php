<?php
$servername = "localhost";
$username = "root";  // Change this if needed
$password = "mypass";  // Change this if needed
$database = "ctecomco_ranmal";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



?>
