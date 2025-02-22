<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "localhost";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "saloon_db"; // Name of your database

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$appointment_confirmed = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $full_name = $_POST['full_name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $appointment_time = $_POST['appointment_time'];
    $service = $_POST['service']; // Selected service

    // Check if the selected appointment time is a Sunday
    $appointment_day = date('l', strtotime($appointment_time)); // Get the day of the week
    if ($appointment_day == "Sunday") {
        $error_message = "Sorry, the salon is closed on Sundays. Please choose a different day.";
    } else {
        // Check if the appointment time is during lunch break (1:00 PM to 1:45 PM)
        $appointment_hour = date('H', strtotime($appointment_time));
        $appointment_minute = date('i', strtotime($appointment_time));

        // Check if the appointment time is outside of salon hours (9 AM to 7 PM)
        if ($appointment_hour < 9 || $appointment_hour > 19 || ($appointment_hour == 19 && $appointment_minute > 0)) {
            $error_message = "Sorry, the salon is open from 9:00 AM to 7:00 PM. Please choose a time within this range.";
        } else if (($appointment_hour == 13 && $appointment_minute >= 0 && $appointment_minute < 45)) {
            // Check if the appointment time is during lunch break (1:00 PM to 1:45 PM)
            $error_message = "Sorry, the salon is closed during lunch break from 1:00 PM to 1:45 PM. Please choose a different time.";
        } else {
            // Check if the appointment time is already booked
            $sql_check = "SELECT * FROM appointments WHERE appointment_time = '$appointment_time'";
            $result = $conn->query($sql_check);
            
            if ($result->num_rows > 0) {
                $error_message = "Sorry, this appointment time is already taken. Please choose a different time.";
            } else {
                // Insert data into database
                $sql_insert = "INSERT INTO appointments (full_name, address, email, phone, appointment_time, service) 
                            VALUES ('$full_name', '$address', '$email', '$phone', '$appointment_time', '$service')";

                if ($conn->query($sql_insert) === TRUE) {
                    // Data inserted successfully
                    $appointment_confirmed = true;  // Flag to show the confirmation message after form submission
                } else {
                    $error_message = "Error: " . $sql_insert . "<br>" . $conn->error;
                }
            }
        }
    }
    
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Saloonspear</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="datetime-local"],
        input[type="submit"],
        select {
            padding: 12px;
            margin-bottom: 18px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #0d25c1;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #003f9d;
        }

        .error-message,
        .success-message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .error-message {
            background-color: #f44336;
            color: white;
        }

        .success-message {
            background-color: #4caf50;
            color: white;
        }

        .go-back-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            padding: 12px 25px;
            background-color: #0d25c1;
            color: white;
            font-size: 16px;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .go-back-btn:hover {
            background-color: #003f9d;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            input[type="submit"], .go-back-btn {
                font-size: 14px;
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <?php if (isset($appointment_confirmed) && $appointment_confirmed): ?>
            <!-- Success Message after Form Submission -->
            <h2>Appointment Booked Successfully!</h2>
            <div class="success-message">
                Your appointment has been booked successfully. Please check your email for confirmation.
            </div>
            <a href="home.php" class="go-back-btn">Go Back to Home</a>
        <?php elseif ($error_message): ?>
            <!-- Error Message -->
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
            <a href="booking.php" class="go-back-btn">Go Back and Try Again</a>
        <?php else: ?>
            <!-- Booking Form -->
            <h2>Book Your Appointment</h2>
            <form action="booking.php" method="post">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required><br>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="phone">Phone :</label>
                <input type="text" id="phone" name="phone" pattern="\d{10}" title="Phone number must be 10 digits" required><br>

                <label for="appointment_time">Appointment Time:</label>
                <input type="datetime-local" id="appointment_time" name="appointment_time" required><br>

                <label for="service">Select Service:</label>
                <select id="service" name="service" required>
                    <option value="Haircut">Haircut</option>
                    <option value="Facial">Facial</option>
                    <option value="Manicure">Manicure</option>
                    <option value="Pedicure">Pedicure</option>
                    <!-- Add more services as needed -->
                </select><br>

                <input type="submit" value="Confirm Appointment">
            </form>
            <!-- Go Back Button -->
            <a href="home.php" class="go-back-btn">Go Back to Home</a>
        <?php endif; ?>
    </div>

</body>
</html>
