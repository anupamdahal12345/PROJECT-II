<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require('connection.php'); 

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    $manufacture_date = mysqli_real_escape_string($conn, $_POST['manufacture_date']);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Check if the file is an image
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_ext, $allowed_ext)) {
            // Set image path and move the uploaded file to the target directory
            $image_path = 'uploads/' . time() . '_' . basename($image_name);
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                // File successfully uploaded, save the product data into the database
                $sql = "INSERT INTO products (product_id, name, description, price, category, expiry_date, manufacture_date, image) 
                        VALUES ('$product_id', '$name', '$description', '$price', '$category', '$expiry_date', '$manufacture_date', '$image_path')";
                
                if (mysqli_query($conn, $sql)) {
                    echo "<script>alert('Product added successfully!'); window.location.href='product_tab.php';</script>";
                    exit();
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "Invalid image format. Allowed formats: jpg, jpeg, png, gif.";
        }
    } else {
        echo "Image is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        nav {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin: 0 10px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #343a40;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], input[type="date"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        #expiry_date_container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
        <li><a href="ad_admin.php" class="active">Home</a></li>
            <li><a href="ad_product.php" class="active">Products</a></li>
            <li><a href="add_product.php" class="active">Add Product</a></li> <!-- Add Product Link -->
            <li><a href="remove_product.php" class="active">Remove Product</a></li> <!-- Remove Product Link -->
            <li><a href="view_booking.php" class="active">View bookings</a></li>
            <li><a href="add_service.php" class="active">Add Service</a></li>
            <li><a href="remove_service.php" class="active">Remove Service</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <h1>Add New Product</h1>

    <form method="POST" action="add_product.php" enctype="multipart/form-data">
        <label for="product_id">Product ID:</label>
        <input type="text" name="product_id" required>

        <label for="name">Product Name:</label>
        <input type="text" name="name" required>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" required>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="" disabled selected>Select Category</option>
            <option value="cosmetic">Cosmetic</option>
            <option value="electronic">Electronic</option>
        </select>

        <div id="expiry_date_container" style="display: none;">
            <label for="expiry_date">Expiry Date:</label>
            <input type="date" name="expiry_date">

            <label for="manufacture_date">Manufacture Date:</label>
            <input type="date" name="manufacture_date">
        </div>

        <label for="image">Product Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit">Add Product</button>
    </form>

    <script>
        document.getElementById('category').addEventListener('change', function () {
            var category = this.value;
            var expiryDateContainer = document.getElementById('expiry_date_container');
            if (category === 'cosmetic') {
                expiryDateContainer.style.display = 'block';
            } else {
                expiryDateContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
