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

// Function to add a new service
function addService($name, $description, $image, $price) {
    global $conn;

    // Upload image
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($image["name"]);
    move_uploaded_file($image["tmp_name"], $targetFile);

    // Insert service details into the database
    $sql = "INSERT INTO services (name, description, image_path, is_available, price) VALUES (?, ?, ?, 1, ?)";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    // Bind parameters
    $stmt->bind_param("sssd", $name, $description, $targetFile, $price);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = $_POST["service_name"];
    $serviceDescription = $_POST["service_description"];
    $serviceImage = $_FILES["service_image"];
    $servicePrice = floatval($_POST["service_price"]);

    if (addService($serviceName, $serviceDescription, $serviceImage, $servicePrice)) {
        echo "Service added successfully.";
    } else {
        echo "Error adding service.";
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            /* padding-bottom: 100px; */
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
            
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Form element styling */
        .form-field {
            margin-bottom: 15px;
        }

        .form-field label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-field input[type="text"],
        .form-field textarea,
        .form-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; 
        }

        .form-field input[type="file"] {
            border: none;
        }

        /* Button styling */
        .form-button {
            background-color: #3200a0;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-button:hover {
            background-color: #5434c8;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                margin: 20px auto;
            }
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
            <div class="form-container">
                <h2>Add New Service</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-field">
                        <label for="service_name">Service Name:</label>
                        <input type="text" name="service_name" required>
                    </div>
                    <div class="form-field">
                        <label for="service_description">Service Description:</label>
                        <textarea name="service_description" required></textarea>
                    </div>
                    <div class="form-field">
                        <label for="service_image">Service Image:</label>
                        <input type="file" name="service_image" accept="image/*" required>
                    </div>
                    <div class="form-field">
                        <label for="service_price">Price (in Rs):</label>
                        <input type="text" name="service_price" required>
                    </div>
                    <input type="submit" value="Add Service">
                </form>
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
        // Current year for the footer copyright
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
        // Smooth scroll functionality
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Animate elements when they come into view
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.service-item, .feature-item, .testimonial-item');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Set initial state for animation
        document.querySelectorAll('.service-item, .feature-item, .testimonial-item').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'all 0.6s ease';
        });
        
        // Run on load and scroll
        window.addEventListener('load', animateOnScroll);
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>
