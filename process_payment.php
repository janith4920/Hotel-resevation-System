<?php
include 'dbconnection.php';

// Get form data
$roomtype = $_POST['roomtype'];
$price = $_POST['price'];
$checkin = $_POST['checkin'];
$checkout = $_POST['checkout'];
$adults = $_POST['adults'];
$kids = $_POST['kids'];
$rooms = $_POST['rooms'];
$board_option = $_POST['board_option'];

// Build URL with parameters
$redirect_url = 'generate_pdf.php?roomtype=' . urlencode($roomtype) . 
                '&price=' . urlencode($price) . 
                '&checkin=' . urlencode($checkin) . 
                '&checkout=' . urlencode($checkout) . 
                '&adults=' . urlencode($adults) . 
                '&kids=' . urlencode($kids) . 
                '&rooms=' . urlencode($rooms) . 
                '&board_option=' . urlencode($board_option);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment</title>
    <script>
        window.onload = function() {
            window.open('<?php echo $redirect_url; ?>', '_blank');
            window.location.href = 'index.html'; // Redirect back to home page
        }
    </script>
</head>
<body>
    <p>Processing your payment...</p>
</body>
</html> 