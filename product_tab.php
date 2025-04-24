<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Include the database connection
require('connection.php');

// Function to fetch all products
function getAllProducts($conn) {
    $products = array();
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

$productsList = getAllProducts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
    <style>
        /* Main Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        /* Navbar styles */
        nav {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        nav ul li a.active {
            font-weight: bold;
        }

        /* Container styles */
        .home {
            text-align: center;
            margin: 20px 0;
        }

        /* Logout button at bottom right */
        .logout {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout:hover {
            background-color: #555;
        }

        /* Main Content */
        main {
            padding: 20px;
            text-align: center;
        }

        section {
            margin-top: 20px;
        }

        /* Product List styles */
        .product-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .product-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin: 10px;
            padding: 15px;
            width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 10px rgba(0, 0, 0, 0.2);
        }

        .product-item img {
            width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .product-item p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .no-products {
            font-size: 18px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav>
        <label class="cont">SalonSpear</label>
        <ul>
        
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="product_tab.php">Products</a></li>
            <li><a href="my_booking.php" class="active">My appointments</a></li>
            <li><a href="cancel_service.php" class="active">Cancel appointments</a></li>
            <li><a class="active" id="contact-btn">Contact Us</a></li>
            
        </ul> 
    </nav>
    <div class="home">
        <button><a class="logout" href="logout.php"> LOGOUT </a></button>
    </div>

    <main>
        <section>
            <?php
            if (!empty($productsList)) {
                echo '<ul class="product-list">';
                foreach ($productsList as $product) {
                    echo '<li class="product-item">';
                    
                    // Display product image
                    if (!empty($product['image'])) {
                        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="Product Image"/>';
                    }

                    // Display product details
                    echo '<p><strong>Name:</strong> ' . htmlspecialchars($product['name']) . '</p>';
                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($product['description']) . '</p>';
                    echo '<p><strong>Price:</strong> Rs. ' . htmlspecialchars($product['price']) . '</p>';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($product['category']) . '</p>';

                    // Display expiry and manufacture dates only for 'cosmetic' category and valid dates
                    if (strtolower($product['category']) === 'cosmetic') {
                        if (!empty($product['expiry_date']) && $product['expiry_date'] !== '0000-00-00') {
                            echo '<p><strong>Expiry Date:</strong> ' . htmlspecialchars($product['expiry_date']) . '</p>';
                        }
                        if (!empty($product['manufacture_date']) && $product['manufacture_date'] !== '0000-00-00') {
                            echo '<p><strong>Manufacture Date:</strong> ' . htmlspecialchars($product['manufacture_date']) . '</p>';
                        }
                    }

                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p class="no-products">No products available.</p>';
            }
            ?>
        </section>
    </main>
</body>
</html>