<?php
// payment.php

// Retrieve the parameters from the URL (or a session)
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
$kids = isset($_GET['kids']) ? intval($_GET['kids']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;
$roomType = isset($_GET['roomtype']) ? htmlspecialchars($_GET['roomtype']) : 'Room';

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

// Format guests summary (e.g., "1 Room, 2 Adults, 1 Kid")
$guestSummary = "$rooms Room, $adults Adult" . ($adults != 1 ? 's' : '') . ", $kids Kid" . ($kids != 1 ? 's' : '');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Moon</title>
    <link rel="stylesheet" href="pay.css">
</head>

<body>
    <div class="container">
        <div class="navbar">
            
            <div class="navdiv">
                <h2 class="logo">Hotel Moon</h2>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Rooms</a></li>
                    <li><a href="#">Gallery</a></li>
                    <li><a href="#">Info</a></li>
                </ul>
            </div>
            
        </div>
        
        <div class="booking-form">
            
                <p>Your Stay</p>
                <p>|</p>
                <p>Hotel Moon</p>
                <p>|</p>

                <p><?php echo $roomType; ?></p> <!-- Dynamic room type -->

                <p>|</p>
                <p><?php echo htmlspecialchars($dateSummary); ?></p>
                <p>|</p>
                <p><?php echo htmlspecialchars($guestSummary); ?></p>
                <p>|</p>
                <button class="check-btn" onclick="window.location.href='index.html'">Edit Stay</button>

        </div>
    <div class="payment-container">
        <div class="payment">
            <h1>Payment and Guest Details</h1>
            <hr class="line1">
            <br>
            <div class="pricing">
                <p class="stay"><strong>Total for stay</strong> <span class="price"></span></p>
                <p>Total room charge <span class="roomprice"></span></p>
                <p>Total Service Charge <span class="serviceprice">Rs.0.00</span></p>
                <p>Other Charges <span class="otherprice">Rs.0.00</span></p>
            </div>
            <br>
            <hr class="line2">
           
            <div class="progress-container">
                <p class="hotel-name">Hotel Moon</p>
                <div class="progress-bar">
                    <div class="step completed">
                        <span class="dot"></span>
                        <p>Date/Guests</p>
                    </div>
                    <div class="line completed"></div>
                    <div class="step completed">
                        <span class="dot"></span>
                        <p>VIP Room</p>
                    </div>
                    <div class="line"></div>
                    <div class="step">
                        <span class="dot"></span>
                        <p>Payment</p>
                    </div>
                </div>
            </div>
    </div>
    </div>

    <div class="details-container">
        <!-- Title section -->
        <div class="title">Payment Details</div>
        <hr>
        <div class="content">
          <!-- Registration form -->
          <form method="post" action="process_payment.php" id="bookingForm">
            <input type="hidden" name="roomtype" value="<?php echo htmlspecialchars($roomType); ?>">
            <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
            <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
            <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
            <input type="hidden" name="adults" value="<?php echo htmlspecialchars($adults); ?>">
            <input type="hidden" name="kids" value="<?php echo htmlspecialchars($kids); ?>">
            <input type="hidden" name="rooms" value="<?php echo htmlspecialchars($rooms); ?>">
            <input type="hidden" name="board_option" value="<?php echo htmlspecialchars($board_option); ?>">
            
            <div class="user-details">
              <!-- Input for Full Name -->
              <div class="input-box">
                <span class="details">Card Number</span>
                <input type="text" placeholder="Enter card number" name="cardnum" required>
              </div>
              <!-- Input for Username -->
              <div class="input-box">
                <span class="details">Month/Year</span>
                <input type="text" placeholder="MM/YY" name="monthyear" required>
              </div>
        </div>
              <div class="title">Guest Details</div>
              <hr/>
              <div class="content">
                <!-- Registration form -->
                <form action="#">
                  <div class="user-details">
                    <!-- Input for Full Name -->
                    <div class="input-box">
                      <span class="details">First Name</span>
                      <input type="text" placeholder="Enter your first name" name="firstname" required>
                    </div>
                    <!-- Input for Username -->
                    <div class="input-box">
                      <span class="details">Last Name</span>
                      <input type="text" placeholder="Enter your last name" name="lastname" required>
                    </div>
                  </div>
                </div>

                <div class="content">
                    <!-- Registration form -->
                    <form action="#">
                      <div class="user-details">
                        <!-- Input for Full Name -->
                        <div class="input-box">
                          <span class="details">Email</span>
                          <input type="text" placeholder="example@gmail.com" name="email" required>
                        </div>
                        <!-- Input for Username -->
                        <div class="input-box">
                          <span class="details">Phone</span>
                          <input type="text" placeholder="+947 *** *** **" name="phone" required>
                        </div>
                      </div>
                    </div>

                    <div class="content">
                        <!-- Registration form -->
                        <form action="#">
                          <div class="user-details">
                            <!-- Input for Full Name -->
                            <div class="input-box">
                              <span class="details">Country/Region</span>
                              <input type="text" placeholder="Srilanka" name="country" required>
                            </div>
                            <!-- Input for Username -->
                            <div class="input-box">
                              <!-- <span class="details">Month/Year</span>
                              <input type="text" placeholder="Enter your username" required> -->
                            </div>
                          </div>
                        </div>

                        <div class="content">
                            <!-- Registration form -->
                            <form action="#">
                              <div class="user-details">
                                <!-- Input for Full Name -->
                                <div class="input-box">
                                  <span class="details">Address</span>
                                  <input type="text" placeholder="Enter your address" name="address" required>
                                </div>
                                <!-- Input for Username -->
                                <div class="input-box">
                                  <span class="details">Address2</span>
                                  <input type="text" placeholder="Enter your address" name="address2" >
                                </div>
                              </div>
                            </div>

                            <div class="content">
                                <!-- Registration form -->
                                <form action="#">
                                  <div class="user-details">
                                    <!-- Input for Full Name -->
                                    <div class="input-box">
                                      <span class="details">ZIP</span>
                                      <input type="text" placeholder="#####" name="zip" required>
                                    </div>
                                    <!-- Input for Username -->
                                    <div class="input-box">
                                      <span class="details">City</span>
                                      <input type="text" placeholder="Enter your city" name="city" required>
                                    </div>
                                  </div>
                                </div>

                                <div class="content">
                                    <!-- Registration form -->
                                    <form action="#">
                                      <div class="user-details">
                                        <!-- Input for Full Name -->
                                        <div class="input-box">
                                          <span class="details">State</span>
                                          <input type="text" placeholder="Enter your state" name="state" required>
                                        </div>
                                        <!-- Input for Username -->
                                        <div class="input-box">
                                          <!-- <span class="details">Month/Year</span>
                                          <input type="text" placeholder="Enter your username" required> -->
                                        </div>
                                      </div>
                                    </div>

                                    <div class="content">
                                        <!-- Special Request field -->
                                        <div class="user-details">
                                            <!-- Input for Special Request -->
                                            <div class="textarea">
                                              <span class="details">Special Request</span>
                                              <input type="text" placeholder="special reqest for resevation" name="special">
                                            </div>
                                        </div>
                                    </div>

            <!-- Submit button -->
            <div class="confirmbtn">
              <button type="submit" class="confirm" onclick="printConfirmation(event)">Confirm Booking</button>
            </div>
          </form>
        </div>
      </div>

      <!-- <div class="generate-pdf">
        <button class="pdf-btn" onclick="window.print()">Download Reservation Confirmation</button>
      </div> -->
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const params = new URLSearchParams(window.location.search);
        const price = params.get("price");
    
        // Update the room price from the URL parameter
        if (price) {
            const roomPriceSpan = document.querySelector('.roomprice');
            const totalPriceSpan = document.querySelector('.price');
            const priceInput = document.querySelector('input[name="price"]');
            
            if (roomPriceSpan) {
                roomPriceSpan.textContent = "Rs." + price;
            }
            if (totalPriceSpan) {
                totalPriceSpan.textContent = "Rs." + price;
            }
            if (priceInput) {
                priceInput.value = price;
            }
        }
    });

    function printConfirmation(event) {
        event.preventDefault();
        
        // Get form data
        const formData = new FormData(document.getElementById('bookingForm'));
        
        // Check required fields
        const requiredFields = {
            'firstname': 'First Name',
            'lastname': 'Last Name',
            'email': 'Email',
            'phone': 'Phone',
            'country': 'Country',
            'address': 'Address',
            'zip': 'ZIP Code',
            'city': 'City',
            'state': 'State'
        };

        let missingFields = [];
        for (let [field, label] of Object.entries(requiredFields)) {
            if (!formData.get(field) || formData.get(field).trim() === '') {
                missingFields.push(label);
            }
        }

        if (missingFields.length > 0) {
            alert('Please fill in all required fields:\n' + missingFields.join('\n'));
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.get('email'))) {
            alert('Please enter a valid email address');
            return;
        }

        // Validate phone number (basic validation)
        const phoneRegex = /^\+?[\d\s-]{10,}$/;
        if (!phoneRegex.test(formData.get('phone'))) {
            alert('Please enter a valid phone number');
            return;
        }

        // Create a form for PDF generation
        const pdfForm = document.createElement('form');
        pdfForm.method = 'POST';
        pdfForm.action = 'generate_pdf.php';
        pdfForm.target = '_blank';

        // Add all form fields to the PDF form
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            pdfForm.appendChild(input);
        }

        // Add price to the PDF form
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'price';
        priceInput.value = new URLSearchParams(window.location.search).get('price') || '';
        pdfForm.appendChild(priceInput);

        // Add the form to the document and submit it
        document.body.appendChild(pdfForm);
        pdfForm.submit();
        document.body.removeChild(pdfForm);

        // Submit the original form for payment processing
        document.getElementById('bookingForm').submit();
    }
    </script>
        
</body>

</html>