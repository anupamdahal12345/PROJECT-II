<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'saloon_db';

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get available salon services
function getSalonServices() {
    global $conn;
    $services = [];

    $sql = "SELECT * FROM services";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }

    return $services;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saloonspear - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgba(240, 255, 251, 0.37); /* Light blue background */
            margin: 0;
            padding: 0;
        }
        nav {
            background:rgb(13, 37, 193);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }
        .logo {
            font-size: 50px;
            font-weight: bold;
            color: white;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            padding: 7px 10px;
            border-radius: 4px;
            transition: 0.3s;
        }
        nav ul li a:hover {
            background: blue;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }
        .service-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .service-item {
            background: white;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
        }
        .service-item img {
            max-width: 100%;
            border-radius: 8px;
        }
        .book-btn {
            display: block;
            margin: 20px auto;
            padding: 12px 20px;
            background:rgb(255, 34, 34);
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            width: 200px;
            font-size: 18px;
            transition: 0.3s;
        }
        .book-btn:hover {
            background:rgb(230, 25, 25);
        }
        footer {
            text-align: center;
            padding: 15px;
            background: #333;
            color: white;
            margin-top: 20px;
        }

        /* Contact Section */
        #contact {
            padding: 50px;
            background-color: #f1f1f1;
            margin-top: 50px;
        }
        #contact h3 {
            margin-bottom: 20px;
        }
        #contact p {
            font-size: 18px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">Saloonspear</div>
    <ul>
        <li><a href="home.php" class="active">Home</a></li>
        <li><a href="booking.php">Book Appointment</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="products.php">Products</a></li> <!-- New Products Tab -->
    </ul>
</nav>


    <div class="container">
        <h2>Welcome to Saloonspear</h2>
        <p>Your one-stop solution for all your grooming needs!</p>

        <h3>Our Services</h3>
        <div class="service-container">
            <?php
            $services = getSalonServices();
            if (!empty($services)) {
                foreach ($services as $service) {
                    
                    if ($service['name'] == 'Haircut') {
                        $image = 'images/haircut.jpg'; // Sample image for Haircut
                    } elseif ($service['name'] == 'Beard Trim') {
                        $image = 'images/beard_trim.jpg'; // Sample image for Beard Trim
                    } elseif ($service['name'] == 'Facial') {
                        $image = 'images/facial.jpg'; // Sample image for Facial
                    } else {
                        $image = $service['image_path']; // Use the image from the database
                    }

                    echo '<div class="service-item">';
                    echo '<img src="' . $image . '" alt="' . $service['name'] . '">';
                    echo '<h4>' . $service['name'] . '</h4>';
                    echo '<p>' . $service['description'] . '</p>';
                    echo '<p><strong>Price: Rs.' . $service['price'] . '</strong></p>';
                    echo '<a href="booking.php?service_id=' . $service['id'] . '" class="book-btn">Book Now</a>';
                    echo '</div>';
                }
            } else {
                echo "<p>No services available at the moment.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Contact Section -->
    <div id="contact">
        <h3>Contact Us</h3>
        <p>If you have any questions or need further information, feel free to reach out to us:</p>

        <h4>Our Contact Information</h4>
        <p><strong>Email:</strong> saloonspear@gmail.com</p>
        <p><strong>Phone:</strong> +9779812567789</p>
        <p><strong>Address:</strong> Mahendrapool, Pokhara</p>

        <h4>Our Business Hours</h4>
        <p>Sunday to Friday: 9:00 AM - 7:00 PM</p>
        <p>Saturday: Closed</p>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Saloonspear - All Rights Reserved.
    </footer>

</body>
</html>
