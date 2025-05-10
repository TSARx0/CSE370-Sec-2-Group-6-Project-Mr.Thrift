<?php
// DB Connection
$host = "localhost";
$user = "root";
$password = ""; // change this to your password
$dbname = "cse370";
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cart product details
$cart_sql = "SELECT Products.* FROM Cart 
             JOIN Products ON Cart.Product_ID = Products.Product_ID";
$cart_result = $conn->query($cart_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .top-nav {
            display: flex; justify-content: space-between; align-items: center;
            background-color: #f1f1f1; padding: 10px 20px;
        }
        .top-left, .top-center, .top-right {
            display: flex; align-items: center; gap: 15px;
        }
        a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #ddd;
        }
        .cart-section {
            display: flex; flex-direction: column; padding: 20px;
        }
        .cart-header {
            display: flex; justify-content: space-between; align-items: center;
        }
        .cart-list {
            margin-top: 20px;
        }
        .cart-item {
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;
        }
        .cart-item img { width: 100px; height: auto; }
        .cart-details {
            flex: 1; margin-left: 15px;
        }
        .cart-actions {
            text-align: right;
        }
        .cart-actions button {
            padding: 8px 12px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .cart-actions button:hover {
            background-color: #c0392b;
        }
        .checkout-btn {
            margin-top: 30px;
            padding: 15px 25px;
            background-color: #2ecc71;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .checkout-btn:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>

<!-- Section 1: Top Navigation -->
<div class="top-nav">
    <div class="top-left">
        <a href="home.php">Home</a>
    </div>
    <div class="top-center">
        <a href="notification.php">🔔</a>
    </div>
    <div class="top-right">
        <a href="leaderboard.php">Leader Board</a>
        <a href="user.php">👤<br>User</a>
    </div>
</div>

<!-- Section 2: My Cart -->
<div class="cart-section">
    <div class="cart-header">
        <a href="javascript:history.back()">&lt; Back</a>
        <h2>My Cart</h2>
        <div></div>
    </div>

    <div class="cart-list">
        <?php if ($cart_result->num_rows > 0): ?>
            <?php while($product = $cart_result->fetch_assoc()): ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($product['Image']) ?>" alt="<?= htmlspecialchars($product['Name']) ?>">
                    <div class="cart-details">
                        <strong><?= htmlspecialchars($product['Name']) ?></strong><br>
                        Category: <?= htmlspecialchars($product['Category']) ?><br>
                        Price: $<?= htmlspecialchars($product['Price']) ?>
                    </div>
                    <div class="cart-actions">
                        <form method="POST" action="remove_from_cart.php"> <!-- remove configure kori nai  -->
                            <input type="hidden" name="product_id" value="<?= $product['Product_ID'] ?>">
                            <button type="submit">🗑️ Remove</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div style="text-align: center;">
        <a href="checkout.php"><button class="checkout-btn">Check Out</button></a>  <!-- checkout configure kori nai  -->
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
