<?php
// Example product data (can be fetched from the database if you have a products table)
$products = [
    [
        'name' => 'Hair Dryer',
        'description' => 'A high-quality hair dryer with multiple speed settings.',
        'price' => 'Rs. 1500',
        'image' => 'products/hair_dryer.jpg'
    ],
    [
        'name' => 'Trimmer',
        'description' => 'A precision trimmer for facial hair and beard grooming.',
        'price' => 'Rs. 800',
        'image' => 'products/trimmer.jpg'
    ],
    [
        'name' => 'Cleanser',
        'description' => 'A gentle facial cleanser for daily use.',
        'price' => 'Rs. 300',
        'image' => 'products/cleanser.jpg'
    ],
    [
        'name' => 'Beard Foam',
        'description' => 'Rich foam for beard grooming and maintenance.',
        'price' => 'Rs. 400',
        'image' => 'products/beard_foam.jpg'
    ],
    [
        'name' => 'Hair Wax',
        'description' => 'A strong-hold hair wax for styling and shaping.',
        'price' => 'Rs. 500',
        'image' => 'products/hair_wax.jpg'
    ],
    [
        'name' => 'Hair Styling Powder',
        'description' => 'Texturizing powder for added volume and style.',
        'price' => 'Rs. 350',
        'image' => 'products/hair_styling_powder.jpg'
    ],
    [
        'name' => 'Shampoo',
        'description' => 'Shampoo for healthy and shiny hair.',
        'price' => 'Rs. 250',
        'image' => 'products/shampoo.jpg'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Saloonspear</title>
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
        .product-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .product-item {
            background: white;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
        }
        .product-item img {
            max-width: 100%;
            border-radius: 8px;
        }
        footer {
            text-align: center;
            padding: 15px;
            background: #333;
            color: white;
            margin-top: 20px;
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
        <h2>Our Products</h2>
        <p>Explore our range of grooming products available for sale:</p>

        <div class="product-container">
            <?php
            foreach ($products as $product) {
                echo '<div class="product-item">';
                echo '<img src="' . $product['image'] . '" alt="' . $product['name'] . '">';
                echo '<h4>' . $product['name'] . '</h4>';
                echo '<p>' . $product['description'] . '</p>';
                echo '<p><strong>Price: ' . $product['price'] . '</strong></p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Saloonspear - All Rights Reserved.
    </footer>

</body>
</html>
