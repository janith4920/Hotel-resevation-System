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

// Initialize total adults and kids
$totalAdults = 0;
$totalKids = 0;

// Process multiple rooms if they exist
for ($i = 1; $i <= $rooms; $i++) {
    $roomAdults = isset($_GET["room{$i}_adults"]) ? intval($_GET["room{$i}_adults"]) : 1;
    $roomKids = isset($_GET["room{$i}_kids"]) ? intval($_GET["room{$i}_kids"]) : 0;
    
    $totalAdults += $roomAdults;
    $totalKids += $roomKids;
}

// Fallback to original parameters if new format isn't found
if ($totalAdults == 0) {
    $totalAdults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
}

if ($totalKids == 0) {
    $totalKids = isset($_GET['kids']) ? intval($_GET['kids']) : 0;
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
            margin-top: 10px;
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
      <!-- <a href="payment.php" class="button" >Book Now</a> -->
      <button class="booknow" onclick="window.location.href='payment.php?roomtype=<?php echo urlencode($popupRoomType); ?> &price=<?php echo urlencode($row['rctrate']); ?>&checkin=<?php echo urlencode($_GET['checkin']); ?>&checkout=<?php echo urlencode($_GET['checkout']); ?>&adults=<?php echo urlencode($_GET['adults']); ?>&kids=<?php echo urlencode($_GET['kids']); ?>&rooms=<?php echo urlencode($_GET['rooms']); ?>'">
        Book From Rs<?php echo htmlspecialchars($row['rctrate']); ?>
        </button>
    </div>

  </div>
  
</div>



<!-- Room Cards -->
<div class="card-container">
<?php 
while ($row = $result->fetch_assoc()) { 
    // Retrieve and sanitize the room name
    $roomName = htmlspecialchars($row['rctrct']);
    $roomDescription = htmlspecialchars($row['rctdes']);
    $rctsno          = (int) $row['rctsno'];
    
    // Determine the image path and popup room type based on the room name
    if (stripos($roomName, 'standard') !== false) {
        $imagePath = 'images/standard_00.png';
        $popupRoomType = 'Standard Room';
    } elseif (stripos($roomName, 'delux') !== false) {
        $imagePath = 'images/delux_00.png';
        $popupRoomType = 'Delux Room';
    } elseif (stripos($roomName, 'vip') !== false) {
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
        <!-- Pass the correct room type to the showPopup function -->
        <!-- <p class="view-details" onclick='showPopup(<//?php echo json_encode($popupRoomType); ?>, <//?php echo $jsDescription; ?>, <//?php echo $jsAmenities; ?>)'> -->
        <button class="view-details" onclick='showPopup(<?php echo json_encode($popupRoomType); ?>, <?php echo json_encode($roomDescription); ?>, <?php echo json_encode($roomAmenities); ?>, <?php echo htmlspecialchars($row['rctrate']); ?>)'>

          View room details
          <button class="menuitems" onclick="window.location.href='http://124.43.176.52/hotel_menu_pdf/pdf.php'">Menu Items</button>

        </p>
        <button class="booknow" onclick="window.location.href='payment.php?roomtype=<?php echo urlencode($popupRoomType); ?> &price=<?php echo urlencode($row['rctrate']); ?>&checkin=<?php echo urlencode($_GET['checkin']); ?>&checkout=<?php echo urlencode($_GET['checkout']); ?>&adults=<?php echo urlencode($_GET['adults']); ?>&kids=<?php echo urlencode($_GET['kids']); ?>&rooms=<?php echo urlencode($_GET['rooms']); ?>'">
        Book From Rs<?php echo htmlspecialchars($row['rctrate']); ?>
        </button>
      </div>
    </div>
<?php } ?>  
</div>

    <!-- <div class="card">
        <img src="delus.png" alt="Room 2">
        <div class="card-content">
        <h3>//</h3>
            <p class="view-details" onclick="showPopup('Delux Room')">View room details</p>
            <button class="booknow" onclick="window.location.href='payment.html'">Book From $115</button>
        </div>
    </div>
    <div class="card">
        <img src="vip.png" alt="Room 3">
        <div class="card-content">
            <h3>VIP Room</h3>
            <p class="view-details" onclick="showPopup('VIP Room')">View room details</p>
            <button class="booknow" onclick="window.location.href='payment.html'">Book From $115</button>
        </div>
    </div>
</div> -->

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
    document.getElementById('popupDescription').textContent = dbDescription || room.description;

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

    // // 4) Inject dynamic amenities
    // const amenitiesContainer = document.getElementById('amenitiesContainer');
    //   amenitiesContainer.innerHTML = ""; // clear old amenities
    //   amenitiesList.forEach(function(amenity) {
    //     let span = document.createElement('span');
    //     span.classList.add('amenity');
    //     span.textContent = amenity;
    //     amenitiesContainer.appendChild(span);
    //   });
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

  // Update the Book Now link in the popup to include all the parameters
  const bookNowLink = document.querySelector('.popup-content a.button');
  if (bookNowLink) {
    bookNowLink.href = 'payment.php?price=' + encodeURIComponent(roomRate) +
      '&checkin=' + encodeURIComponent(checkin) +
      '&checkout=' + encodeURIComponent(checkout) +
      '&adults=' + encodeURIComponent(adults) +
      '&kids=' + encodeURIComponent(kids) +
      '&rooms=' + encodeURIComponent(rooms);
  } else {
    console.error("Popup Book Now link not found");
  }

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



</script>
</body>
</html>
<?php
$conn->close();
?>
