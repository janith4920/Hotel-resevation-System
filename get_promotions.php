<?php
include 'dbconnection.php';

// Fetch promotions from f_rsp table
$promo_sql = "SELECT rspdes FROM f_rspmast WHERE rspdes IS NOT NULL";
$promo_result = $conn->query($promo_sql);
$promotions = array();

if ($promo_result) {
    while ($row = $promo_result->fetch_assoc()) {
        $promotions[] = $row['rspdes'];
    }
}

// Return promotions as JSON
header('Content-Type: application/json');
echo json_encode($promotions);

$conn->close();
?> 