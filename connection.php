<?php
    // Get POST data
    $firstName = $_POST['firstname'];
    $lastName  = $_POST['lastname'];
    $phone     = $_POST['phone'];
    $email     = $_POST['email'];
    $country   = $_POST['country'];
    $address   = $_POST['address'];
    $address2  = $_POST['address2'];
    $zip       = $_POST['zip'];
    $city      = $_POST['city'];
    $state     = $_POST['state'];
    $special   = $_POST['special'];

    // Update these credentials with your actual MySQL credentials
    $servername = "localhost";
    $username   = "root";      // change to your username
    $password   = "mypass";          // change to your password if applicable
    $dbname     = "ctecomco_ranmal";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind statement
    $stmt = $conn->prepare("INSERT INTO reservation_details (F_name, L_name, Email, Phone, `CountryRegion`, aaddress, Address2, ZIP, City, Sstate, Request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssisssisss", $firstName, $lastName, $email, $phone, $country, $address, $address2, $zip, $city, $state, $special); 

    // Execute statement
    if ($stmt->execute()) {
        echo "<script>
                alert('New record created successfully');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
?>

