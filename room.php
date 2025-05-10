<?php
include 'dbconnection.php';  // Include the connection file

// Modified query to select specific columns including image data
// $sql = "SELECT rctrct, rctdes FROM f_rctmast WHERE rctrate IS NOT NULL";
 //$sql = "SELECT rctrct, rctdes, rctrate FROM f_rctmast WHERE rctrate IS NOT NULL";
// $sql = "SELECT rctrct, image_data, rctrate FROM f_rctmast WHERE rctrate IS NOT NULL";
// $result = $conn->query($sql);

// if (!$result) {
//     die("Query failed: " . $conn->error);
// }

// 1) Fetch all rooms that have a rate, also fetch rctsno so we can join on it
$sql = "SELECT rctrct, rctdes, rctrate, rctsno 
        FROM f_rctmast 
        WHERE rctrate IS NOT NULL";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// 2) Fetch amenities from f_rcfmast, referencing f_rctmast.rctsno
$amenities_sql = "
    SELECT rcfdes, rcfrctsno
    FROM f_rcfmast
";
$amenities_result = $conn->query($amenities_sql);
if (!$amenities_result) {
    die("Amenities query failed: " . $conn->error);
}

// 3) Build an associative array of amenities keyed by rctsno
$amenitiesData = array();
while ($am_row = $amenities_result->fetch_assoc()) {
    $fk_rctsno = $am_row['rcfrctsno'];  // foreign key to f_rctmast.rctsno
    $amenity   = $am_row['rcfdes'];

    // If no array exists yet for this rctsno, initialize it
    if (!isset($amenitiesData[$fk_rctsno])) {
        $amenitiesData[$fk_rctsno] = array();
    }
    // Add the amenity to this room's amenity array
    $amenitiesData[$fk_rctsno][] = $amenity;
}

// Retrieve parameters from URL
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;
$boardOption = isset($_GET['board_option']) ? $_GET['board_option'] : 'Room Only'; // Default to Room Only

// Initialize total adults and kids
$totalAdults = 0;
$totalKids = 0;

// Process multiple rooms if they exist
for ($i = 1; $i <= $rooms; $i++) {
    $adults = isset($_GET["room{$i}_adults"]) ? intval($_GET["room{$i}_adults"]) : 1;
    $kids = isset($_GET["room{$i}_kids"]) ? intval($_GET["room{$i}_kids"]) : 0;
    
    $totalAdults += $adults;
    $totalKids += $kids;
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

// Check for rooms matching requirements
$stmt->bind_param("iii", $totalAdults, $totalKids, $boardTypeId);
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

// Format guests summary (e.g., "1 Room, 2 Adults, 1 Kid")
$guestSummary = "$rooms Room" . ($rooms != 1 ? 's' : '') . ", $totalAdults Adult" . ($totalAdults != 1 ? 's' : '') . ", $totalKids Kid" . ($totalKids != 1 ? 's' : '');

// Format the dates (e.g., "Mar 14 Fri - Mar 15 Sat")
$dateSummary = '';
if (!empty($checkin) && !empty($checkout)) {
    try {
        $checkinDate = new DateTime($checkin);
        $checkoutDate = new DateTime($checkout);
        $formattedCheckin = $checkinDate->format('M d D');
        $formattedCheckout = $checkoutDate->format('M d D');
        $dateSummary = "$formattedCheckin - $formattedCheckout";
    } catch (Exception $e) {
        $dateSummary = 'Invalid date';
    }
} else {
    $dateSummary = 'Select Dates';
}

// Fetch promotions from f_rsp table
$promo_sql = "SELECT rspdes FROM f_rspmast WHERE rspdes IS NOT NULL";
$promo_result = $conn->query($promo_sql);
$promotions = array();

if ($promo_result) {
    while ($row = $promo_result->fetch_assoc()) {
        $promotions[] = $row['rspdes'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Moon</title>
    <style>
        body {
            margin: 0;
        }
        .navbar {
            background: #0073e6;
            padding-right: 15px;
            padding-left: 15px;
            padding-top: 15px;
        }
        .navdiv {
            display: flex;
            align-items: center; 
            justify-content: space-between;
            /* margin-left: 70svw; */
        }
        li {
            list-style: none;
            display: inline-block;
        }
        li a {
            color: white;
            font-size: 18px;
            margin-right: 25px;
            text-decoration: none;
        }
        .logo {
            color: white;
            /* font-size: 4rem; */
            margin-bottom: 20px;
            text-align: center;
        }

        .booking-form {
            display: flex;
            margin-top: 15px;
            margin-right: auto;
            margin-left: auto;
            max-width: 50%;
            background: white;
            padding: 10px 20px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #0073e6;
            gap:0;
    
        }
        .booking-form p{
            font-size: 20px;
            font-style: bold;
        }

        .check-btn {
            background-color: #0073e6;  
            padding: 10px 20px;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
            border: none;
        }


        
        .card-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .card{
            align-items: center;
            width: 450px;
            background-color: aliceblue;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
            margin: 20px;
            padding-bottom: 20px;
        }

        .card-content{
            padding-left: 10px;
            padding-right: 10px;
        }

        .card-content h3{
            font-size: 28px;
            margin-bottom: 8px;
        }

        .card-content a{
            color: #000;
            font-size: 15px;
            line-height: 1.3;
            padding-left: 10px;
        }


        .booknow{
            background-color: #0073e6;  
            padding: 10px 20px;
            width: 100%;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
            border: none; 
            margin-left: auto;
            margin-right: auto;
        }

 
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            /* display: flex; */
            justify-content: center;
            align-items: center;
            
        }

        .popup {
            background: #fff;
            margin-top: 3%;
            /* position: fixed;
            top: 0; */
            left: 3%; 
            position: relative;
            padding: 20px;
            border-radius: 8px;
            width: 900px;
            width: 90%;
            text-align: center;
            display: flex;
            gap: 20px;
            justify-self: unset;
        }

        .close-btn {
            position: absolute;
            top: 2px;
            right: 6px;
            cursor: pointer;
            font-size: 24px;
            color: red;
            
        }

        .popup-left {
            width: 50%;
        }

        .popup-right {
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .button{
            background-color: #0073e6; 
            text-decoration:none; 
            padding: 10px 20px;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
            border: none;
            margin: 10px;

        }


        .image-slider {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
        }

        .slides {
            position: relative;
            width: 100%;
        }

        .slide {
            display: none;
            width: 100%;
            border-radius: 5px;
        }

        .slide.active {
            display: block;
        }

        .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
        }

        .prev-btn {
            left: 10px;
        }

        .next-btn {
            right: 10px;
        }

        .popup-content{
            display: flex;
            flex-direction: column;
             height: 100%; 
             margin-top: 20px;
        }

        .popup-right .button{
            margin-top: auto;
            margin-top: 20px;
        }

        .popup-content h2 {
            margin: 10px 0;
            font-size: 40px;
        }

        .popup-content p {
            font-size: 20px;
            text-align: left;
            margin-bottom: 10px;
        }

        /* .amenities {
            display: flex;
            flex-direction: column; 
            gap: 5px;
            margin-top: 20px;
            max-width: 200px;
        } */

        /* .amenity {
            display: inline-block;
            background: none;
            color: #0073e6;
            padding: 5px 10px;
            margin: 5px;
            font-size: 12px;
            border: 1px solid #0073e6;
        } */

        /* Container that holds all columns (amenitiesContainer) */
        .amenities {
        display: flex;         /* So columns appear side by side */
        gap: 50px;               /* Spacing between columns */
        flex-wrap: wrap;   
        justify-content: center;      /* Wrap if there are more than 2 columns */             
        }

        /* Each chunk of 5 amenities becomes one column */
        .amenity-column {
        display: flex;
        flex-direction: column;
        gap: 10px;  /* Stack amenities vertically */
        }

        /* Amenities themselves on separate lines */
        /* .amenity {
        display: block;
        background: none;
        color: #0073e6;
        padding: 5px 10px;
        margin: 5px 0;
        font-size: 12px;
        border: 1px solid #0073e6;
        } */
        .amenity {
            display: block;
            padding: 5px 10px;
            margin: 5px 0;
            font-size: 18px;
            color:rgb(0, 94, 187);
            position: relative;
            text-align: left;
        }

        .amenity::before {
            content: "• ";
            color: #0073e6;
            margin-right: 5px;
            position: absolute;
            left: 0;
        }

        .view-details {
            background: none;
            border: none;
            padding-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            text-decoration: underline;
            color: #0073e6;
        }

        .menuitems{
            float: right;
            background : none;  
            padding: 10px 20px;
            border-radius: 5px;
            border: 2px solid #0073e6;
            border-color: #0073e6;
            color: #0073e6;
            cursor: pointer;
            margin-bottom: 10px;

        }

        .promotion{
            float: right ;
            margin-right: 50px;
            margin-top: 30px;
        }
        .promo-btn{
            transform: skew(0deg, -15deg);
            font-weight:600;
            border:0px;   
            text-transform: uppercase;
            letter-spacing:3px;
            padding:15px 25px 15px 25px;
            cursor:pointer;
            background:#ffc600;
            color:#ffffff;
        }
        @property --angle{
            syntax: "<angle>";
            initial-value: 0deg;
            inherits: false;
            }
        .promo-btn:hover{
            background:#333;
            color:#ffc600;  
            border:1px dashed #ffc600;
        }
        .promo-btn::after, .promo-btn::before{
            content: '';
            position: absolute;
            height: 100%;
            width: 100%;
            background-image: conic-gradient(from var(--angle), #fe0000, #0073e6);
            top: 50%;
            left: 50%;
            translate: -50% -50%;
            z-index: -1;
            padding: 3px;
            border-radius: 10px;
            animation: 1s spin linear infinite;
            }
            .promo-btn::before{
            filter: blur(1.5rem);
            opacity: 0.5;
            }
            @keyframes spin{
            from{
                --angle: 0deg;
            }
            to{
                --angle: 360deg;
            }
            }

        /* Promotion Popup Styles */
        .promo-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .promo-popup {
            background: #0073e6b3;
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 600px;
            position: relative;
            animation: popupFadeIn 0.3s ease-out;
        }

        @keyframes popupFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .promo-popup h2 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }

        .promo-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .promo-item {
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .promo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.5);
        }

        .promo-item h3 {
            color: #ffffff;
            margin-bottom: 10px;
            font-size: 25px;
        }

        .promo-item p {
            color: #ff8989;
            margin: 0;
            line-height: 1.5;
        }

        .promo-popup .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            color: #ff0000;
            cursor: pointer;
            transition: color 0.2s;
            background-color: rgba(255, 255, 255, 0.542);
            width: 30px;
            text-align: center;
            border-radius: 10px;
        }

        .promo-popup .close-btn:hover {
            color: #ffffff;
            background-color: rgb(255, 0, 0);
        }

        .special-promo{
            text-align: center;
            margin: 15px;
        }
        .special-promo-btn{
            background: linear-gradient(135deg, #ff0000, #ff6b6b);
            padding: 10px 20px;
            color: #ffffff;
            font-size: 40px;
            cursor: pointer;
            border: none;
            width: 53.5%;
            height: 87px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .special-promo-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4);
            background: linear-gradient(135deg, #ff6b6b, #ff0000);
        }

        .special-promo-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.5s;
        }

        .special-promo-btn:hover::before {
            left: 100%;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
            }
        }

        .special-promo-btn {
            animation: pulse 2s infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-5px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .special-promo-btn:hover {
            animation: float 2s ease-in-out infinite;
        }
 </style>

</head>

<body>
    <div class="container">
        <div class="navbar">
            
            <div class="navdiv">
                <h2 class="logo">Hotel Moon</h2>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="#">Rooms</a></li>
                    <li><a href="#">Gallery</a></li>
                    <li><a href="#">Info</a></li>
                </ul>
            </div>
            
        </div>
        
        <!-- <div class="booking-form">
            <div class="date" id="checkInDate">
                <p>MAR<br><strong>14</strong><br>FRI</p>
                <input type="date" id="checkInInput" style="display: none;">
            </div>
            <div class="date" id="checkOutDate">
                <p>MAR<br><strong>15</strong><br>FRI</p>
                <input type="date" id="checkOutInput" style="display: none;">
            </div>
            <button class="guests" id="openPopup">1 Room, 1 Guest</button>
            <button class="check-btn">Check Rooms And Rates</button>
        </div> -->
        <!-- <div class="booking-form">
            <p>Your Stay</p>
            <p>|</p>
            <p>Hotel Moon</p>
            <p>|</p>
            <p id="summaryDates">""</p>
            <p>|</p>
            <p id="summaryGuests">""</p>
            <p>|</p>
             <button class="check-btn">Edit Stay</button> 
            <button class="check-btn" onclick="window.location.href='hotelcopy.html'">Edit Stay</button>
        </div> -->
        <div class="booking-form">
            <p>Your Stay</p>
            <p>|</p>
            <p>Hotel Moon</p>
            <p>|</p>
            <div id="summaryDates" class="date-container">
                <?php echo htmlspecialchars($dateSummary); ?>
            </div>
            <p>|</p>
            <div id="summaryGuests" class="guest-container">
                <?php echo htmlspecialchars($guestSummary); ?>
            </div>
            <p>|</p>
            <button class="check-btn" onclick="window.location.href='index.html'">Edit Stay</button>
    </div>


    </div>

    <div class="special-promo">
        <button class="special-promo-btn">Today's Special Promotions</button>
    </div>


    
    <!-- Popup -->
    <!-- <div class="popup-overlay" id="popupOverlay">
        <div class="popup">
            <span class="close-btn" id="closePopup">&times;</span>
            <div class="image-slider">
            <div class="slides" id="popupSlides">
                 
            </div>
            <button class="slider-btn prev-btn" onclick="prevSlide()">&#10094;</button>
            <button class="slider-btn next-btn" onclick="nextSlide()">&#10095;</button>
        </div>
        <div class="popup-content">
            <h2 id="popupTitle"></h2>
            <p class="popupdescription" id="popupDescription"></p>
            <a href="payment.html" class="button">Book Now</a>
            <div class="amenities" id="amenitiesContainer">
            </div>
        </div>
        </div>
    </div> -->
<div class="popup-overlay" id="popupOverlay">
  <div class="popup">
    <span class="close-btn" id="closePopup">&times;</span>
    
    <div class="popup-left">
      <!-- Image Slider -->
      <div class="image-slider">
        <div class="slides" id="popupSlides">
          <!-- Images inserted dynamically -->
        </div>
        <button class="slider-btn prev-btn" onclick="prevSlide()">&#10094;</button>
        <button class="slider-btn next-btn" onclick="nextSlide()">&#10095;</button>
      </div>
    </div>

    <div class="popup-right popup-content">
      <h2 id="popupTitle"></h2>
      <p class="popupdescription" id="popupDescription"></p>
      <div class="amenities" id="amenitiesContainer"></div>
      <button id="popupBookBtn" class="booknow">
        Book Now
        </button>
    </div>

  </div>
  
</div>



<!-- Room Cards -->
<div class="card-container">
<?php 
// Display only available rooms that match the capacity requirements
foreach ($availableRooms as $room) { 
    $roomName = htmlspecialchars($room['room_type']);
    $roomDescription = htmlspecialchars($room['description']);
    $rctsno = (int) $room['room_id'];
    
    // Determine the image path and popup room type based on the exact room type from database
    if ($roomName == 'STANDARD') {
        $imagePath = 'images/standard_00.png';
        $popupRoomType = 'Standard Room';
    } elseif ($roomName == 'DELUX') {
        $imagePath = 'images/delux_00.png';
        $popupRoomType = 'Delux Room';
    } elseif ($roomName == 'VIP') {
        $imagePath = 'images/vip_00.png';
        $popupRoomType = 'VIP Room';
    } else {
        $imagePath = 'images/default-room.jpg';
        $popupRoomType = 'Standard Room';
    }

     // Grab the amenities for this room from $amenitiesData
    $roomAmenities = isset($amenitiesData[$rctsno]) ? $amenitiesData[$rctsno] : array();

    // Convert the PHP array of amenities to JSON for JS
    $jsAmenities = json_encode($roomAmenities);
?>
    <div class="card">
      <img src="<?php echo $imagePath; ?>" alt="<?php echo $roomName; ?> Image" style="width: 100%; height: 300px; object-fit: cover;">   
      <div class="card-content">
        <h3><?php echo $roomName; ?></h3>
        <button class="view-details" onclick='showPopup(<?php echo json_encode($popupRoomType); ?>, <?php echo json_encode($roomDescription); ?>, <?php echo json_encode($roomAmenities); ?>, <?php echo htmlspecialchars($room['rate']); ?>)'>
          View room details
          <button class="menuitems" onclick="window.location.href='http://124.43.176.52/hotel_menu_pdf/pdf.php'">Menu Items</button>
        </p>
        <button class="booknow" onclick="window.location.href='payment.php?roomtype=<?php echo urlencode($popupRoomType); ?>&price=<?php echo urlencode($room['rate']); ?>&checkin=<?php echo urlencode($_GET['checkin']); ?>&checkout=<?php echo urlencode($_GET['checkout']); ?>&adults=<?php echo urlencode($totalAdults); ?>&kids=<?php echo urlencode($totalKids); ?>&rooms=<?php echo urlencode($rooms); ?>&board_option=<?php echo urlencode($boardOption); ?>'">
        Book From Rs<?php echo htmlspecialchars($room['rate']); ?>
        </button>
      </div>
    </div>
<?php } ?>  

</div>

    <div class="promotion">
            <button class="promo-btn">Today's <br> Promotions</button>
        </div>

    <!-- Promotion Popup -->
    <div class="promo-popup-overlay" id="promoPopupOverlay">
        <div class="promo-popup">
            <span class="close-btn" id="closePromoPopup">&times;</span>
            <h2>Today's Special Promotions</h2>
            <div class="promo-content">
                <?php foreach ($promotions as $promo): ?>
                <div class="promo-item">
                    <h3><?php echo htmlspecialchars($promo); ?></h3>
    </div>
                <?php endforeach; ?>
        </div>
    </div>
    </div>



<!-- JavaScript -->
<script>
let currentSlideIndex = 0;

const roomData = {
  "Standard Room": {
    images: ["images/standard_01.jpg", "images/standard_02.jpg", "images/standard_03.jpg"]
  },
  "Delux Room": {
    images: ["images/delux_04.jpg", "images/delux_05.jpg", "images/delux_06.jpg"]
  },
  "VIP Room": {
    images: ["images/vip_01.jpg", "images/vip_02.jpg", "images/vip_03.jpg"]
  }
};

function showPopup(roomType, dbDescription, amenitiesList, roomRate) {
  const room = roomData[roomType];

  if (room) {
    // Update popup title and description
    document.getElementById('popupTitle').textContent = roomType;
    document.getElementById('popupDescription').textContent = dbDescription;

    // Clear previous images and add new ones
    const slidesContainer = document.getElementById('popupSlides');
    slidesContainer.innerHTML = ""; // Clear any existing images

    room.images.forEach((imageSrc, index) => {
      const imgElement = document.createElement("img");
      imgElement.src = imageSrc;
      imgElement.alt = `${roomType} Image ${index + 1}`;
      imgElement.classList.add("slide");
      if (index === 0) imgElement.classList.add("active"); // Make the first image active
      slidesContainer.appendChild(imgElement);
    });

    // Reset slide index
    currentSlideIndex = 0;

            // Clear old amenities
            const amenitiesContainer = document.getElementById('amenitiesContainer');
        amenitiesContainer.innerHTML = "";

        // Break amenitiesList into groups of 5
        const chunkSize = 5;
        for (let i = 0; i < amenitiesList.length; i += chunkSize) {
            // Slice out a chunk of up to 5 amenities
            const chunk = amenitiesList.slice(i, i + chunkSize);

            // Create a new column container
            const columnDiv = document.createElement('div');
            columnDiv.classList.add('amenity-column');

            // Add each amenity as a <span>
            chunk.forEach((amenity) => {
                const span = document.createElement('span');
                span.classList.add('amenity');
                span.textContent = amenity;
                columnDiv.appendChild(span);
            });

            // Append this column to the main container
            amenitiesContainer.appendChild(columnDiv);
        }

    // Update the Book Now button with the correct URL and price
    const popupBookBtn = document.getElementById('popupBookBtn');
    popupBookBtn.textContent = `Book From Rs${roomRate}`;
    popupBookBtn.onclick = function() {
      window.location.href = 'payment.php?roomtype=' + encodeURIComponent(roomType) +
        '&price=' + encodeURIComponent(roomRate) +
        '&checkin=' + encodeURIComponent(<?php echo json_encode($_GET['checkin']); ?>) +
        '&checkout=' + encodeURIComponent(<?php echo json_encode($_GET['checkout']); ?>) +
        '&adults=' + encodeURIComponent(<?php echo json_encode($totalAdults); ?>) +
        '&kids=' + encodeURIComponent(<?php echo json_encode($totalKids); ?>) +
        '&rooms=' + encodeURIComponent(<?php echo json_encode($rooms); ?>) +
        '&board_option=' + encodeURIComponent(<?php echo json_encode($boardOption); ?>);
    };

    // Display the popup
    document.getElementById('popupOverlay').style.display = 'block';
  }
}

function closePopup() {
  document.getElementById('popupOverlay').style.display = 'none';
}

document.getElementById('closePopup').addEventListener("click", closePopup);

function nextSlide() {
  const slides = document.querySelectorAll("#popupSlides .slide");
  if (slides.length === 0) return;
  slides[currentSlideIndex].classList.remove("active");
  currentSlideIndex = (currentSlideIndex + 1) % slides.length;
  slides[currentSlideIndex].classList.add("active");
}

function prevSlide() {
  const slides = document.querySelectorAll("#popupSlides .slide");
  if (slides.length === 0) return;
  slides[currentSlideIndex].classList.remove("active");
  currentSlideIndex = (currentSlideIndex - 1 + slides.length) % slides.length;
  slides[currentSlideIndex].classList.add("active");
}

// Promotion popup functionality
const promoBtn = document.querySelector('.promo-btn');
const promoPopupOverlay = document.getElementById('promoPopupOverlay');
const closePromoBtn = document.getElementById('closePromoPopup');

promoBtn.addEventListener('click', () => {
    promoPopupOverlay.style.display = 'flex';
});

closePromoBtn.addEventListener('click', () => {
    promoPopupOverlay.style.display = 'none';
});

// Close promotion popup when clicking outside
promoPopupOverlay.addEventListener('click', (e) => {
    if (e.target === promoPopupOverlay) {
        promoPopupOverlay.style.display = 'none';
    }
});

</script>

</body>
</html>
<?php
$conn->close();
?>
