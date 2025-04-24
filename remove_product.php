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

// Handle product removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

    // Delete the product from the database
    $sql = "DELETE FROM products WHERE product_id = '$product_id'";

    if (mysqli_query($conn, $sql)) {
        // Product deleted successfully, redirect back to this page
        header('Location: remove_product.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch all products from the database
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Product</title>
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

        .product-list {
            max-width: 1000px;
            margin: 20px auto;
        }

        .product-item {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .product-item img {
            max-width: 150px;
            margin-right: 20px;
        }

        .product-item h2 {
            margin-top: 0;
        }

        .product-item p {
            margin: 5px 0;
        }

        .remove-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="ad_product.php">Products</a></li>
            <li><a href="add_product.php">Add Product</a></li>
            <li><a href="remove_product.php" class="active">Remove Product</a></li>
            <li><a href="view_booking.php">View Bookings</a></li>
            <li><a href="add_service.php">Add Service</a></li>
            <li><a href="remove_service.php">Remove Service</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <h1>Remove Product</h1>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-item">
                <div style="display: flex;">
                    <img src="<?php echo $product['image']; ?>" alt="Product Image">
                    <div>
                        <h2><?php echo $product['name']; ?></h2>
                        <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                        <p><strong>Price:</strong> Rs. <?php echo number_format($product['price'], 2); ?></p>
                        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                        <?php if ($product['category'] == 'cosmetic'): ?>
                        <p><strong>Manufacture Date:</strong> <?php echo $product['manufacture_date']; ?></p>
                        
                            <p><strong>Expiry Date:</strong> <?php echo $product['expiry_date']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="POST" action="remove_product.php" style="margin-top: 10px;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <button type="submit" name="remove_product" class="remove-btn">Remove Product</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
