<?php
include 'dbconnection.php';

// Get parameters from POST data
$roomtype = isset($_POST['roomtype']) ? $_POST['roomtype'] : '';
$price = isset($_POST['price']) ? $_POST['price'] : '';
$checkin = isset($_POST['checkin']) ? $_POST['checkin'] : '';
$checkout = isset($_POST['checkout']) ? $_POST['checkout'] : '';
$adults = isset($_POST['adults']) ? $_POST['adults'] : '';
$kids = isset($_POST['kids']) ? $_POST['kids'] : '';
$rooms = isset($_POST['rooms']) ? $_POST['rooms'] : '';
$board_option = isset($_POST['board_option']) ? $_POST['board_option'] : '';

// Get guest details from POST data
$firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
$lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$country = isset($_POST['country']) ? $_POST['country'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
$address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
$zip = isset($_POST['zip']) ? $_POST['zip'] : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$state = isset($_POST['state']) ? $_POST['state'] : '';
$special = isset($_POST['special']) ? $_POST['special'] : '';

// Generate a random confirmation number
$confirmation_number = rand(1000000000, 9999999999);
$pin_code = rand(1000, 9999);

// Format dates
$checkin_date = new DateTime($checkin);
$checkout_date = new DateTime($checkout);

// If POST data is empty, try GET data (for backward compatibility)
if (empty($roomtype)) {
    $roomtype = isset($_GET['roomtype']) ? $_GET['roomtype'] : '';
    $price = isset($_GET['price']) ? $_GET['price'] : '';
    $checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
    $checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
    $adults = isset($_GET['adults']) ? $_GET['adults'] : '';
    $kids = isset($_GET['kids']) ? $_GET['kids'] : '';
    $rooms = isset($_GET['rooms']) ? $_GET['rooms'] : '';
    $board_option = isset($_GET['board_option']) ? $_GET['board_option'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: poppins, sans-serif;
            line-height: 1.6;
            margin: 40px;
            border: 2px solid #003580;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 40px;
            color: #003580;
            font-weight: bold;
        }
        .hotel-info {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 3px solid #003580;
            border-top: 3px solid #003580;
            padding: 10px;
        }
        .hotel-details {
            flex: 1;
        }
        .booking-dates {
            display: flex;
            justify-content: space-between;
            border: 10px solid #003580;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .date-box {
            text-align: center;
            flex: 1;
            padding: 0 15px;
            border-right: 1px solid #ccc;
        }
        .date-box:last-child {
            border-right: none;
        }
        .date-number {
            font-size: 30px;
            font-weight: bold;
            color: #003580;
            margin: 10px 0;
        }
        .guests-info {
            margin-top: 10px;
            font-size: 16px;
            color: #003580;
        }
        .price-section {
            margin-bottom: 30px;
        }
        .price-table {
            width: 100%;
            border-collapse: collapse;
        }
        .price-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .total-price {
            font-size: 18px;
            font-weight: bold;
        }
        .guest-info {
            margin-bottom: 30px;
        }
        .important-info {
            background: #f5f5f5;
            padding: 20px;
            margin-bottom: 30px;
        }
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Hotel Moon</div>
    </div>

    <div class="hotel-info">
        <div class="hotel-details">
            <h2>Hotel Moon</h2>
            <p>Address: No.389 Koralawella Road, 10400 Moratuwa, Sri Lanka</p>
            <p>Phone: +94 112 658 442</p>
            <p>GPS coordinates: N 006° 45.031, E 79° 53.587</p>
        </div>
    </div>

    <div class="booking-dates">
        <div class="date-box">
            <div>CHECK-IN</div>
            <div class="date-number"><?php echo $checkin_date->format('d'); ?></div>
            <div><?php echo $checkin_date->format('F'); ?></div>
            <div><?php echo $checkin_date->format('l'); ?></div>
            <div>From 14:00</div>
        </div>
        <div class="date-box">
            <div>CHECK-OUT</div>
            <div class="date-number"><?php echo $checkout_date->format('d'); ?></div>
            <div><?php echo $checkout_date->format('F'); ?></div>
            <div><?php echo $checkout_date->format('l'); ?></div>
            <div>Until 12:00</div>
        </div>
        <div class="date-box">
            <div>ROOMS</div>
            <div class="date-number"><?php echo $rooms; ?></div>
            <div class="guests-info">
                <?php echo $adults; ?> Adult<?php echo $adults != 1 ? 's' : ''; ?> / 
                <?php echo $kids; ?> Kid<?php echo $kids != 1 ? 's' : ''; ?>
            </div>
        </div>
    </div>

    <div class="price-section">
        <h3>PRICE</h3>
        <table class="price-table">
            <tr>
                <td>Room Price (<?php echo htmlspecialchars($roomtype); ?>)</td>
                <td align="right">Rs. <?php echo number_format((float)$price, 2); ?></td>
            </tr>
            <tr>
                <td>Service Charge</td>
                <td align="right">Rs. 0.00</td>
            </tr>
            <tr>
                <td>Other Charges</td>
                <td align="right">Rs. 0.00</td>
            </tr>
            <tr class="total-price">
                <td>Total Price</td>
                <td align="right">Rs. <?php echo number_format((float)$price, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="guest-info">
        <h3>GUEST INFORMATION</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?>
            <?php if (!empty($address2)) echo '<br>' . htmlspecialchars($address2); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($city); ?></p>
        <p><strong>State:</strong> <?php echo htmlspecialchars($state); ?></p>
        <p><strong>Country:</strong> <?php echo htmlspecialchars($country); ?></p>
        <p><strong>ZIP Code:</strong> <?php echo htmlspecialchars($zip); ?></p>
        <?php if (!empty($special)): ?>
        <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($special); ?></p>
        <?php endif; ?>
    </div>

    <div class="important-info">
        <h3>IMPORTANT INFORMATION</h3>
        <ul>
            <li>Check-in time starts at 14:00</li>
            <li>Check-out time is 12:00</li>
            <li>Please present your ID and credit card at check-in</li>
            <li>Free private parking is available on site (reservation is not needed)</li>
            <li>WiFi is available in all areas and is free of charge</li>
        </ul>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print Confirmation
        </button>
    </div>

    <script>
        // Automatically trigger print dialog when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html> 