<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database configuration
$hostname = 'localhost';
$username = 'root'; 
$password = '';
$database = 'salon_db';

// Create a new mysqli connection
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check for overlapping bookings (up to 3 bookings within a 1-hour window)
function hasOverlappingBookings($conn, $serviceId, $bookingDateTime) {
    $time_obj = new DateTime($bookingDateTime);

    // Define the 1-hour window (30 minutes before and after)
    $time_before = clone $time_obj;
    $time_before->modify('-30 minutes');
    $time_before_str = $time_before->format('Y-m-d H:i:s');

    $time_after = clone $time_obj;
    $time_after->modify('+30 minutes');
    $time_after_str = $time_after->format('Y-m-d H:i:s');

    // Count how many bookings exist for the same service in this 1-hour window
    $sql = "SELECT COUNT(*) AS count FROM bookings 
            WHERE service_id = ? 
            AND booking_time BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $serviceId, $time_before_str, $time_after_str);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result !== false) {
        $row = $result->fetch_assoc();
        return $row['count'] >= 3;
    } else {
        echo "ERROR: Could not execute query: $sql. " . $conn->error;
        return false;
    }
}

// Function to book a service
function bookService($conn, $serviceId, $userId, $name, $phone, $bookingDateTime, $notes) {
    // Validate input data
    if (empty($bookingDateTime)) {
        echo "<script>alert('ERROR: Booking date and time are required.');</script>";
        return;
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        echo "<script>alert('ERROR: Phone number must be a 10-digit number.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
        return;
    }

    // Check for overlapping bookings
    if (hasOverlappingBookings($conn, $serviceId, $bookingDateTime)) {
        echo "<script>alert('Sorry! The service is already booked by 3 more customers for the selected time. Please try another time period for your booking.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
        return;
    }

    // Insert booking details into the database
    $sql = "INSERT INTO bookings (service_id, user_id, booking_date, booking_time, name, phone, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iisssss", $serviceId, $userId, $bookingDateTime, $bookingDateTime, $name, $phone, $notes);
        if ($stmt->execute()) {
            // Send confirmation email to the user
            $mail = new PHPMailer(true);
            // SMTP configuration (replace with actual details)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ghimireaadesh2003@gmail.com'; 
            $mail->Password = 'pwoquzmnuccdynwv';    
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465; // 465 for SSL
            
            // Send confirmation email
            $recipientEmail = $_SESSION['email']; 
            $mail->setFrom('your_email@gmail.com', 'Salon Booking Service');
            $mail->addAddress($recipientEmail, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Service Booking Confirmation';
            
            // Retrieve booked service name
            $serviceInfoSql = "SELECT name AS service_name FROM services WHERE id = ?";
            $serviceInfoStmt = $conn->prepare($serviceInfoSql);
            $serviceInfoStmt->bind_param("i", $serviceId);
            $serviceInfoStmt->execute();
            $serviceInfoResult = $serviceInfoStmt->get_result();
            $serviceInfo = $serviceInfoResult->fetch_assoc();
            $serviceName = $serviceInfo['service_name'];

            // Include service info in the email body
            $mail->Body = "Dear $name,<br>Your booking for the service $serviceName has been confirmed!<br>Booking Date and Time: $bookingDateTime<br>Thank you for choosing our salon.";

            try {
                $mail->send();
                echo "<script>alert('Your booking has been confirmed! Confirmation email sent.'); window.location.href='home.php';</script>";
            } catch (Exception $e) {
                echo "Error sending email: " . $mail->ErrorInfo;
            }

            // **Update Loyalty Points after booking**
            $updatePoints = $conn->prepare("
                INSERT INTO loyalty_points (user_id, points)
                VALUES (?, 10)
                ON DUPLICATE KEY UPDATE points = points + 10
            ");
            $updatePoints->bind_param("i", $userId);
            $updatePoints->execute();
        } else {
            echo "ERROR: Could not execute query: $sql. " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ERROR: Could not prepare query: $sql. " . $conn->error;
    }
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Retrieve input data from the form and validate
    $serviceId = $_POST['service_id'] ?? null;
    $userId = $_SESSION['user_id'];
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $serviceDate = $_POST['service_date'] ?? '';  // Booking Date (YYYY-MM-DD)
    $serviceTime = $_POST['service_time'] ?? '';  // Booking Time (HH:MM)
    $notes = $_POST['notes'] ?? '';

    // Check if booking date and time are set
    if (!$serviceDate || !$serviceTime) {
        echo "<script>alert('ERROR: Service date and time are required.'); window.location.href = 'service_details.php?id=$serviceId';</script>";
    } else {
        // Combine date and time into a DATETIME format
        $bookingDateTime = $serviceDate . ' ' . $serviceTime;  // Format: YYYY-MM-DD HH:MM

        // Call the bookService function to process the booking
        bookService($conn, $serviceId, $userId, $name, $phone, $bookingDateTime, $notes);
    }
}

// Close the database connection
$conn->close();
?>

