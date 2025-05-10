<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funda of Web IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="card mt-5">
            <div class="card-body">
                <?php
                // Enable error reporting
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                // Database connection
                $con = mysqli_connect("localhost","root","","f_wbkmast");
                
                // Check connection
                if (!$con) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                echo "Connected successfully<br>";

                // Query execution
                $query = "SELECT * FROM reservation_details";
                $query_run = mysqli_query($con, $query);
                
                // Check for query errors
                if (!$query_run) {
                    die("Query failed: " . mysqli_error($con));
                }
                echo "Query executed successfully<br>";
                echo "Number of rows found: " . mysqli_num_rows($query_run) . "<br>";

                // Display results
                if(mysqli_num_rows($query_run) > 0) {
                    // Display first row for debugging
                    $first_row = mysqli_fetch_assoc($query_run);
                    echo "<h5>First Row Structure:</h5>";
                    echo "<pre>";
                    print_r($first_row);
                    echo "</pre>";
                    
                    // Reset pointer
                    mysqli_data_seek($query_run, 0);
                ?>
                <table class="table table-bordered">
                    <thead>
                        <!-- Your table headers here -->
                    </thead>
				<tr>
                                    <th>Date_from</th>
                                    <th>Date_to</th>
                                    <!-- <th>Room_Type</th>
                                    <th>Room_guest</th>
                                    <th>Card_number</th>
                                    <th>Month_Year</th> -->
                                    <th>F_name</th>
                                    <th>L_name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>CountryRegion</th>
                                    <th>aaddress</th>
                                    <th>Address2</th>
                                    <th>ZIP</th>
                                    <th>City</th>
                                    <th>Sstate</th>
                                    <th>Request</th>
                                </tr>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($query_run)) { ?>
                            <tr>
                                    <td><?php echo $row['Date_from']; ?></td>
                                    <td><?php echo $row['Date_to']; ?></td>
                                    <td><?php echo $row['F_name']; ?></td>
                                    <td><?php echo $row['L_name']; ?></td>
                                    <td><?php echo $row['Email']; ?></td>
                                    <td><?php echo $row['Phone']; ?></td>
                                    <td><?php echo $row['CountryRegion']; ?></td>
                                    <td><?php echo $row['aaddress']; ?></td>
                                    <td><?php echo $row['Address2']; ?></td>
                                    <td><?php echo $row['ZIP']; ?></td>
                                    <td><?php echo $row['City']; ?></td>
                                    <td><?php echo $row['Sstate']; ?></td>
                                    <td><?php echo $row['Request']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
                } else {
                    echo "<p>No records found in the table</p>";
                }
                
                // Close connection
                mysqli_close($con);
                ?>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>