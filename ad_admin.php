<?php
session_start();
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

// Function to get available salon services
function getAvailableServices() {
    global $conn;
    $services = array();

    $sql = "SELECT * FROM services WHERE is_available = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    return $services;
}

// Function for fetching service feedback
function getServiceFeedback($serviceId) {
    global $conn;
    $feedback = [];

    $sql = "SELECT f.*, r.full_name 
            FROM feedback f 
            JOIN registered_users r ON f.user_id = r.id 
            WHERE f.service_id = ? 
            ORDER BY f.submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }

    $stmt->close();
    return $feedback;
}

// Function to get average rating for a service
function getAverageRating($serviceId) {
    global $conn;
    
    $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
            FROM feedback 
            WHERE service_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rating_data = $result->fetch_assoc();
    $stmt->close();
    
    return $rating_data;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalonSpear - Premium Beauty Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <style>
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
        
        /* Hero section */
        .hero-section {
            position: relative;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            overflow: hidden;
            margin-top: 50px;
        }
        
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('salon.jpg') center/cover no-repeat;
            z-index: -1;
        }
        
        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            animation: fadeInUp 1s ease;
        }
        
        .hero-section h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }
        
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .hero-btn {
            display: inline-block;
            background: var(--gradient);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .hero-btn:hover::before {
            opacity: 1;
        }
        
        .hero-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        
        /* Services section */
        .services-section {
            padding: 80px 20px;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
            color: var(--dark-color);
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        .service-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .service-item {
            border-radius: 15px;
            overflow: hidden;
            background-color: white;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
        }
        
        .service-item:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .service-image-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .service-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .service-item:hover .service-image {
            transform: scale(1.1);
        }
        
        .service-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--accent-color);
            color: var(--dark-color);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .service-content {
            padding: 25px;
        }
        
        .service-content h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--dark-color);
            font-weight: 700;
        }
        
        .service-content p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .service-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .service-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .service-duration {
            font-size: 0.9rem;
            color: #888;
        }
        
        .book-now-btn {
            display: inline-block;
            /* background: var(--gradient); */
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .book-now-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); */
            background: var(--secondary-color);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .book-now-btn:hover::before {
            opacity: 1;
        }
        
        .book-now-btn:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        /* About Us section */
        .aboutus {
            background-color: white;
            padding: 80px 30px;
            margin: 80px auto;
            border-radius: 15px;
            max-width: 1200px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .aboutus::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: var(--gradient);
        }
        
        .why {
            text-align: center;
            margin-bottom: 60px;
            color: var(--dark-color);
            position: relative;
        }
        
        .why h1 {
            font-size: 2.5rem;
            font-weight: 700;
            display: inline-block;
        }
        
        .why h1::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
            transition: all 0.3s ease;
            transform: scaleX(0);
            transform-origin: left;
        }
        
        .feature-item:hover::after {
            transform: scaleX(1);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover .feature-icon {
            transform: scale(1.1);
            color: var(--secondary-color);
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .feature-desc {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 20px;
            background: linear-gradient(135deg, rgba(255,107,107,0.1), rgba(78,205,196,0.1));
        }
        
        .testimonial-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-item {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin: 20px;
            position: relative;
        }
        
        .testimonial-item::before {
            content: '\201C';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: rgba(78,205,196,0.2);
            font-family: serif;
            line-height: 1;
        }
        
        .testimonial-content {
            padding-left: 40px;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
            line-height: 1.6;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .author-info h4 {
            margin: 0;
            color: var(--dark-color);
        }
        
        .author-info p {
            margin: 0;
            color: #888;
            font-size: 0.9rem;
        }
        
        /* No services message */
        .no-services {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 10px;
            margin: 30px auto;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .no-services p {
            font-size: 1.2rem;
            color: #666;
        }
        
        /* Floating action button */
        /* .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 100;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        } */
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hero-section h1 {
                font-size: 3rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .service-container {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                height: 80vh;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .service-container {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
        /* Styles for service items with expandable content */

            /* Service Item Styles */
            .service-item {
                position: relative;
                border-radius: 10px;
                overflow: hidden;
                background-color: #fff;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .service-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            }

            .service-image-container {
                position: relative;
                overflow: hidden;
                height: 220px;
            }

            .service-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }

            .service-item:hover .service-image {
                transform: scale(1.05);
            }

            .service-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                background-color: #ff6b6b;
                color: white;
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                z-index: 1;
            }

            .service-content {
                padding: 20px;
                position: relative;
            }

            .service-content h3 {
                margin-top: 0;
                margin-bottom: 10px;
                font-size: 20px;
                color: #333;
            }

            /* Service meta information */
            .service-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .service-price {
                font-weight: 700;
                font-size: 18px;
                color: #444;
            }

            /* Expand/collapse button */
            .expand-btn {
                background: transparent;
                border: none;
                color: #555;
                cursor: pointer;
                font-size: 16px;
                padding: 5px;
                transition: transform 0.3s ease;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .expand-btn:hover {
                background: #f5f5f5;
            }

            .expand-btn[aria-expanded="true"] i {
                transform: rotate(180deg);
            }

            .book-now-btn {
                display: inline-block;
                background-color: #4CAF50;
                color: white;
                padding: 10px 20px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: 600;
                transition: background-color 0.3s ease;
                text-align: center;
            }

            .book-now-btn:hover {
                background-color: #388E3C;
            }

            /* Expanded content section */
            .service-expanded-content {
                padding: 0 20px 20px;
                border-top: 1px solid #eee;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            .expanded-description h4,
            .service-feedback h4 {
                font-size: 18px;
                margin: 15px 0 10px;
                color: #333;
            }

            .expanded-description p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 20px;
            }

            /* Feedback section styles */
            .service-feedback {
                margin-top: 20px;
            }

            .rating-summary {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 5px;
            }

            .average-rating {
                font-size: 28px;
                font-weight: 700;
                color: #333;
                margin-right: 15px;
                min-width: 40px;
            }

            .rating-info {
                flex-grow: 1;
            }

            .rating-stars {
                color: #ffc107;
                margin-bottom: 3px;
            }

            .rating-count {
                font-size: 14px;
                color: #777;
            }

            .feedback-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .feedback-item {
                padding: 15px;
                border-bottom: 1px solid #eee;
            }

            .feedback-item:last-child {
                border-bottom: none;
            }

            .feedback-header {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }

            .user-name {
                font-weight: 600;
                color: #444;
            }

            .feedback-date {
                font-size: 12px;
                color: #888;
            }

            .feedback-rating {
                color: #ffc107;
                margin-bottom: 5px;
            }

            .feedback-comment {
                font-size: 14px;
                line-height: 1.5;
                color: #555;
            }

            .view-all-feedback {
                text-align: center;
                margin-top: 15px;
            }

            .view-all-feedback a {
                color: #4CAF50;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
            }

            .view-all-feedback a:hover {
                text-decoration: underline;
            }

            .no-feedback {
                color: #777;
                font-style: italic;
                text-align: center;
                padding: 15px 0;
            }
            .see-all-btn {
                color: #4CAF50;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                padding: 8px 16px;
                border-radius: 20px;
                transition: background-color 0.3s ease;
            }

            .see-all-btn:hover {
                background-color: #f0f0f0;
                text-decoration: none;
            }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="universal-nav" role="navigation">
        <div class="universal-nav-container">
        <a href="#" class="universal-nav-logo">
        <img src="logo.png" alt="SalonSpear Logo" style="height: 60px; width: auto;">
        </a>

    
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
                        <li><a href="loyality_program.php" class="dropdown-logout-btn">My loyality points</a></li>
                        <li><a href="logout.php" class="dropdown-logout-btn">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <h1>Beauty Redefined</h1>
            <p>Experience luxury and transformation at SalonSpear. Our expert stylists are dedicated to bringing out your best look with personalized services in a relaxing environment.</p>
            <a href="#services" class="hero-btn">Discover Services</a>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <h2 class="section-title">Our Premium Services</h2>
        
        <?php
        $availableServices = getAvailableServices();
        if (!empty($availableServices)) {
            echo '<div class="service-container">';
            foreach ($availableServices as $service) {
                $serviceFeedback = getServiceFeedback($service['id']);
                $ratingData = getAverageRating($service['id']);
                
                echo '<div class="service-item">';
                echo '<div class="service-image-container">';
                echo '<span class="service-badge">Popular</span>';
                echo '<img src="' . $service['image_path'] . '" alt="' . $service['name'] . '" class="service-image">';
                echo '</div>';
                
                echo '<div class="service-content">';
                echo '<h3>' . $service['name'] . '</h3>';
                echo '<div class="service-meta">';
                echo '<span class="service-price">Rs. ' . $service['price'] . '</span>';
                
                // Add expand/collapse button
                echo '<button class="expand-btn" aria-expanded="false" aria-controls="service-details-' . $service['id'] . '">';
                echo '<i class="fas fa-chevron-down"></i>';
                echo '</button>';
                echo '</div>';
                
                echo '<a href="initiate_payment.php?id=' . $service['id'] . '" class="book-now-btn">Book Now</a>';
                echo '</div>';
                
                // Hidden expandable content section
                echo '<div id="service-details-' . $service['id'] . '" class="service-expanded-content" style="display: none;">';
                
                // Service description
                echo '<div class="expanded-description">';
                echo '<h4>Service Description</h4>';
                echo '<p>' . $service['description'] . '</p>';
                echo '</div>';
                
                // Service feedback
                echo '<div class="service-feedback">';
                echo '<h4>Customer Reviews</h4>';
              
                // Rating summary
                echo '<div class="rating-summary">';
                if ($ratingData['total_ratings'] > 0) {
                    echo '<div class="average-rating">' . number_format($ratingData['avg_rating'], 1) . '</div>';
                    echo '<div class="rating-info">';
                    echo '<div class="rating-stars">';
                    
                    $avg = round($ratingData['avg_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $avg) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i - 0.5 <= $avg) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    
                    echo '</div>';
                    echo '<div class="rating-count">' . $ratingData['total_ratings'] . ' reviews</div>';
                    echo '</div>';
                } else {
                    echo '<div class="average-rating">-</div>';
                    echo '<div class="rating-info">';
                    echo '<div class="rating-stars">';
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    echo '</div>';
                    echo '<div class="rating-count">No reviews yet</div>';
                    echo '</div>';
                }
                echo '</div>';
                
                // Display feedbacks
                if (count($serviceFeedback) > 0) {
                    echo '<ul class="feedback-list">';
                    $feedbackCount = 0;
                    
                    foreach ($serviceFeedback as $feedback) {
                        $feedbackCount++;
                        // Show only first 3 feedback items initially
                        $displayStyle = $feedbackCount <= 3 ? '' : 'display: none;';
                        echo '<li class="feedback-item" style="' . $displayStyle . '" data-feedback-id="' . $feedback['id'] . '">';
                        echo '<div class="feedback-header">';
                        echo '<div class="user-name">' . htmlspecialchars($feedback['full_name']) . '</div>';
                        echo '<div class="feedback-date">' . date('F j, Y', strtotime($feedback['submitted_at'])) . '</div>';
                        echo '</div>';
                        echo '<div class="feedback-rating">';
                        
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $feedback['rating']) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        
                        echo '</div>';
                        echo '<div class="feedback-comment">' . nl2br(htmlspecialchars($feedback['comment'])) . '</div>';
                        echo '</li>';
                    }
                    echo '</ul>';

                    // Add "See All Reviews" button if there are more than 3 feedback items
                    if ($feedbackCount > 3) {
                        echo '<div class="view-all-feedback">';
                        echo '<a href="#" class="see-all-btn" data-service-id="' . $service['id'] . '">See All Reviews</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="no-feedback">No reviews for this service yet. Be the first to leave a review!</p>';
                }
                echo '</div>'; // End service-feedback
                echo '</div>'; // End service-expanded-content
                
                echo '</div>'; // End service-item
            }
            echo '</div>'; // End service-container
        } else {
            echo '<div class="no-services"><p>No services available at the moment. Please check back later.</p></div>';
        }
        ?>
    </section>

    <!-- About Us section -->
    <section class="aboutus">
        <div class="why">
            <h1>Why Choose SalonSpear?</h1>
        </div>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-cut"></i>
                </div>
                <h3 class="feature-title">Premium Services</h3>
                <p class="feature-desc">We offer a wide range of beauty and wellness services, from trendy haircuts to rejuvenating spa treatments, ensuring that every client receives the perfect makeover.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="feature-title">Easy Online Booking</h3>
                <p class="feature-desc">Say goodbye to long waits! Our user-friendly online platform allows you to book appointments effortlessly, giving you convenience anytime, anywhere.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3 class="feature-title">Expert Stylists</h3>
                <p class="feature-desc">Our team of highly trained professionals is committed to delivering top-quality services, using the latest trends and techniques to bring out your best look.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Safety First</h3>
                <p class="feature-desc">Your well-being is our priority. We follow strict cleanliness protocols and maintain the highest safety standards for a worry-free salon experience.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <h3 class="feature-title">Rewards Program</h3>
                <p class="feature-desc">Join our loyalty program to unlock special discounts, priority bookings, and exclusive offers as our way of appreciating your trust in SalonSpear.</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-spa"></i>
                </div>
                <h3 class="feature-title">Luxury Experience</h3>
                <p class="feature-desc">Indulge in our luxurious salon environment designed to provide you with ultimate relaxation and pampering during your beauty treatments.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="testimonial-container">
            <h2 class="section-title">What Our Clients Say</h2>
            
            <div class="service-container">
                <div class="testimonial-item">
                    <p class="testimonial-content">SalonSpear transformed my look completely! The stylists are true professionals who listen to what you want and deliver beyond expectations.</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/43.jpg" alt="Client" class="author-avatar">
                        <div class="author-info">
                            <h4>Sarah Johnson</h4>
                            <p>Regular Client</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-item">
                    <p class="testimonial-content">I've been to many salons, but none compare to the experience at SalonSpear. The attention to detail and customer service is exceptional.</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Client" class="author-avatar">
                        <div class="author-info">
                            <h4>Michael Chen</h4>
                            <p>New Client</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-item">
                    <p class="testimonial-content">The online booking system is so convenient, and the results are always amazing. My go-to salon for all beauty treatments!</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Client" class="author-avatar">
                        <div class="author-info">
                            <h4>Emma Rodriguez</h4>
                            <p>VIP Member</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


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

        // Initialize the expand/collapse functionality for service details
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial state for animation
            document.querySelectorAll('.service-item, .feature-item, .testimonial-item').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'all 0.6s ease';
            });
            
            // Add event listeners to all expand buttons
            document.querySelectorAll('.expand-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Get the current state
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    
                    // Toggle the state
                    this.setAttribute('aria-expanded', !isExpanded);
                    
                    // Get the target content element
                    const targetId = this.getAttribute('aria-controls');
                    const targetContent = document.getElementById(targetId);
                    
                    // Toggle display of content
                    if (isExpanded) {
                        // Collapse
                        targetContent.style.display = 'none';
                        this.querySelector('i').classList.remove('fa-chevron-up');
                        this.querySelector('i').classList.add('fa-chevron-down');
                    } else {
                        // Expand
                        targetContent.style.display = 'block';
                        this.querySelector('i').classList.remove('fa-chevron-down');
                        this.querySelector('i').classList.add('fa-chevron-up');
                        
                        // Scroll to make expanded content visible if needed
                        setTimeout(() => {
                            const rect = targetContent.getBoundingClientRect();
                            const isVisible = (
                                rect.top >= 0 &&
                                rect.bottom <= window.innerHeight
                            );
                            
                            if (!isVisible) {
                                targetContent.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'nearest'
                                });
                            }
                        }, 100);
                    }
                });
            });
            
            // Run animations on load
            animateOnScroll();
        });
        // Handle "See All Reviews" button click
        document.querySelectorAll('.see-all-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const serviceId = this.getAttribute('data-service-id');
                const feedbackItems = document.querySelectorAll(`#service-details-${serviceId} .feedback-item`);
                const isExpanded = this.textContent === 'Hide Reviews';
                
                feedbackItems.forEach((item, index) => {
                    if (index >= 3) { // Affect only items beyond the first 3
                        item.style.display = isExpanded ? 'none' : 'block';
                    }
                });
                
                // Toggle button text
                this.textContent = isExpanded ? 'See All Reviews' : 'Hide Reviews';
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