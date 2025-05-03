<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$hostname = 'localhost'; 
$username = 'root'; 
$password = ''; 
$database = 'salon_db';

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to remove a service
function removeService($serviceId) {
    global $conn;

    // Delete the service from the database
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $serviceId);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Process the removal of a service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_service_id"])) {
    $serviceId = intval($_POST["remove_service_id"]);

    if (removeService($serviceId)) {
        echo "<script>alert('Service removed successfully.'); window.location.href='remove_service.php';</script>";
    } else {
        echo "<script>alert('Error removing service.');</script>";
    }
}

// Get logged in user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT full_name, username, email FROM registered_users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Get all services
$services_query = "SELECT id, name, description, image_path, price FROM services";
$services_result = $conn->query($services_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <script src="scripts.js"></script>
    <style>
        /* same for all */
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --accent-color: #FFE66D;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            color: var(--dark-color);
        }
        main{
            margin-top: 100px;
        }
        footer{
            margin-top: 100px;
        }

        /* Form container styling */
        .container{
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .service-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .service-item {
            width: 100%;
            max-width: 300px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .service-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .service-item h3 {
            margin: 10px 0;
            color: #333;
        }

        .service-item p {
            color: #777;
            font-size: 14px;
        }

        .remove-button {
            background-color: #FF6B6B;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .remove-button:hover {
            background-color: #FF3D3D;
        }
    </style>
</head>
<body>
     
<!-- Navigation Bar -->
<nav class="universal-nav" role="navigation">
    <div class="universal-nav-container">
    <a href="#" class="universal-nav-logo">
    <img src="logo.png" alt="SalonSpear Logo" style="height: 60px; width: auto;">
        <button class="universal-nav-toggle" id="universal-mobile-menu" aria-label="Toggle navigation" aria-expanded="false">
            <span class="universal-nav-bar"></span>
            <span class="universal-nav-bar"></span>
            <span class="universal-nav-bar"></span>
        </button>
        <ul class="universal-nav-menu" id="nav-menu">
            <li class="universal-nav-item"><a href="ad_admin.php" class="universal-nav-link">Home</a></li>
            <li class="universal-nav-item"><a href="view_booking.php" class="universal-nav-link">View bookings</a></li>
            <li class="universal-nav-item"><a href="add_service.php" class="universal-nav-link">Add Service</a></li>
            <li class="universal-nav-item"><a href="remove_service.php" class="universal-nav-link">Remove Service</a></li>
            <li class="universal-nav-item"><a href="remove_product.php" class="universal-nav-link">Products</a></li>
            <li class="universal-nav-item"><a href="add_product.php" class="universal-nav-link">Add product</a></li>
            <li class="universal-nav-item"><a href="promo.php" class="universal-nav-link">promo</a></li>
            <li class="universal-nav-item universal-nav-dropdown">
                <a href="#" class="universal-nav-link">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <ul class="universal-dropdown-menu">
                    <li class="user-details">
                        <div class="user-info">
                            <p class="user-name"><strong><?php echo htmlspecialchars($user_data['full_name']); ?></strong></p>
                            <p class="user-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        </div>
                    </li>
                    <li><a href="logout.php" class="dropdown-logout-btn">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- body content -->
<main>
    <div class="container">
        <h2>Remove Service</h2>
        <div class="service-list">
            <?php while($service = $services_result->fetch_assoc()) { ?>
                <div class="service-item">
                    <img src="<?php echo $service['image_path']; ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <p><strong>Price: </strong> Rs. <?php echo number_format($service['price'], 2); ?></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return confirmRemoval();">
                        <input type="hidden" name="remove_service_id" value="<?php echo $service['id']; ?>">
                        <button type="submit" class="remove-button">Remove Service</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="universal-footer">
        <div class="universal-footer-container">
            <div class="universal-footer-row">
                <div class="universal-footer-col">
                    <h4>SalonSpear</h4>
                    <ul>
                        <li><a href="ad_admin.php">Home</a></li>
                        <li><a href="remove_product.php">Products</a></li>
                        <li><a href="view_booking.php">View Bookings</a></li>
                        <!-- <li><a href="service_feedback.php">Give your feedback</a></li> -->
                        <li><a href="loyality_program.php">Loyality Program</a></li>
                    </ul>
                </div>
    

                <div class="universal-footer-col">
                    <h4>Follow Us</h4>
                    <div class="universal-social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>


                <div class="universal-footer-col">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Lamachaur, Pokhara, Nepal</a></li>
                        <li><a href="tel:+9779846464646"><i class="fas fa-phone"></i> +977 9846464646</a></li>
                        <li><a href="mailto:salon@gmail.com"><i class="fas fa-envelope"></i> salon@gmail.com</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Mon-Sat: 9:00 AM - 8:00 PM</a></li>
                    </ul>
                </div>
                
            </div>
    
            <div class="universal-footer-bottom">
                <p>&copy; <span id="current-year"></span> Salonspear. All rights reserved.</p>
            </div>
        </div>
    </footer>

<!-- JavaScript -->
<script src="navbar.js"></script>
<script src="footer.js"></script>
<script>
    document.getElementById('current-year').textContent = new Date().getFullYear();

    function confirmRemoval() {
        return confirm("Are you sure you want to remove this service?");
    }
</script>
</body>
</html>
