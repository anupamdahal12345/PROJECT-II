<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $full_name = $_POST['full_name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $appointment_time = $_POST['appointment_time'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
        $error_message = "Invalid email format. Please enter a valid Gmail address.";
    } else {
        // Send confirmation email to the user
        $subject_user = "Appointment Confirmation - Saloonspear";
        $message_user = "Hello $full_name,\n\nYour appointment at Saloonspear has been confirmed.\n\nDetails:\nAppointment Time: $appointment_time\n\nThank you!";
        $headers_user = "From: saloonspear@gmail.com";

        if (mail($email, $subject_user, $message_user, $headers_user)) {
            // Send notification to the admin
            $subject_admin = "New Appointment Booking - Saloonspear";
            $message_admin = "A new appointment has been booked.\n\nDetails:\nName: $full_name\nAddress: $address\nEmail: $email\nPhone: $phone\nAppointment Time: $appointment_time";
            $headers_admin = "From: saloonspear@gmail.com";

            mail("admin@saloonspear.com", $subject_admin, $message_admin, $headers_admin);

            $success_message = "Your appointment has been confirmed. A confirmation email has been sent to you.";
        } else {
            $error_message = "There was an error in sending the confirmation email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Saloonspear</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Book Your Appointment</h2>
        <?php
        if (isset($error_message)) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        if (isset($success_message)) {
            echo "<p style='color: green;'>$success_message</p>";
        }
        ?>
        <form action="booking.php" method="post">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required><br><br>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required><br><br>

            <label for="appointment_time">Appointment Time:</label>
            <input type="datetime-local" id="appointment_time" name="appointment_time" required><br><br>

            <input type="submit" value="Confirm Appointment">
        </form>
    </div>
</body>
</html>
