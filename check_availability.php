<?php
include 'dbconnection.php';

// Get the POST data from the booking form
$checkin = $_POST['checkin'];
$checkout = $_POST['checkout'];
$rooms = $_POST['rooms'];
$boardOption = isset($_POST['board_option']) ? $_POST['board_option'] : 'Room Only'; // Default to Room Only

// Initialize arrays to store room data
$roomData = array();

// Process each room's data
for ($i = 1; $i <= $rooms; $i++) {
    $adults = $_POST["room{$i}_adults"];
    $kids = $_POST["room{$i}_kids"];
    
    $roomData[] = array(
        'adults' => $adults,
        'kids' => $kids
    );
}

// First, get the board type ID from f_rcbmast
$boardSql = "SELECT rcbsno FROM f_rcbmast WHERE rcbdes = ?";
$boardStmt = $conn->prepare($boardSql);
$boardStmt->bind_param("s", $boardOption);
$boardStmt->execute();
$boardStmt->bind_result($boardTypeId);

// Default to 1 (usually Room Only) if not found
if (!$boardStmt->fetch()) {
    $boardTypeId = 1;
}
$boardStmt->close();

// Query to find available rooms that match the capacity and board type requirements
$sql = "SELECT r.rctrct, r.rctdes, f.rcrrate, r.rctsno, f.rcradults, f.rcrkids 
        FROM f_rcrmast f
        JOIN f_rctmast r ON f.rcrrctsno = r.rctsno
        WHERE f.rcradults = ? AND f.rcrkids = ? 
        AND f.rcrrcbsno = ? 
        AND f.rcrrate IS NOT NULL
        ORDER BY f.rcrrate ASC";

$stmt = $conn->prepare($sql);
$availableRooms = array();

// Check each room's requirements against available rooms
foreach ($roomData as $room) {
    $stmt->bind_param("iii", $room['adults'], $room['kids'], $boardTypeId);
    $stmt->execute();
    
    // Bind result variables
    $roomType = '';
    $roomDesc = '';
    $roomRate = 0;
    $roomId = 0;
    $maxAdults = 0;
    $maxKids = 0;
    $stmt->bind_result($roomType, $roomDesc, $roomRate, $roomId, $maxAdults, $maxKids);
    
    // Fetch results and store in array
    while ($stmt->fetch()) {
        $availableRooms[] = array(
            'room_type' => $roomType,
            'description' => $roomDesc,
            'rate' => $roomRate,
            'room_id' => $roomId,
            'max_adults' => $maxAdults,
            'max_kids' => $maxKids
        );
    }
}

// Remove duplicates but no need to sort since we're sorting in SQL
$availableRooms = array_unique($availableRooms, SORT_REGULAR);

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($availableRooms);

$stmt->close();
$conn->close();
?> 