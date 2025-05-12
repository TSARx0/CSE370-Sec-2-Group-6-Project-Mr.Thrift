<?php
session_start();
$User_ID = $_SESSION['User_ID'] ?? 1; // For testing

$host = "localhost";
$username = "root";
$password = "";
$database = "cse370";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if user is a seller
$sellerQuery = $conn->prepare("SELECT * FROM user_is_buyer_and_seller WHERE User_ID = ?");
$sellerQuery->bind_param("i", $User_ID);
$sellerQuery->execute();
$isSeller = $sellerQuery->get_result()->num_rows > 0;
$sellerQuery->close();

// Handle form submission
$confirmationMessage = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $division = $_POST['division'] ?? '';
    $city = $_POST['city'] ?? '';
    $area = $_POST['area'] ?? '';
    $street = $_POST['street'] ?? '';
    $address = "$street, $area, $city, $division";

    // Save order info to a sample Order table (you should create this)
    $insertOrder = $conn->prepare("INSERT INTO Orders (User_ID, Address, Total_Amount) VALUES (?, ?, ?)");
    $insertOrder->bind_param("isd", $User_ID, $address, $total);
    if ($insertOrder->execute()) {
        $confirmationMessage = "✅ Your order has been placed successfully!";
        // Optionally: clear cart
        $clearCart = $conn->prepare("DELETE FROM Cart WHERE User_ID = ?");
        $clearCart->bind_param("i", $User_ID);
        $clearCart->execute();
        $clearCart->close();
    } else {
        $confirmationMessage = "❌ Failed to place the order. Try again.";
    }
    $insertOrder->close();
}

// Get cart items with product info
$cartItemsQuery = $conn->prepare("SELECT Products.Product_ID, Products.Name, Products.Price, Products.Image FROM Cart JOIN Products ON Cart.Product_ID = Products.Product_ID WHERE Cart.User_ID = ?");
$cartItemsQuery->bind_param("i", $User_ID);
$cartItemsQuery->execute();
$cartItems = $cartItemsQuery->get_result();

$total = 0;
$items = [];
while ($row = $cartItems->fetch_assoc()) {
    $total += $row['Price'];
    $items[] = $row;
}
$cartItemsQuery->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .top-nav { display: flex; justify-content: space-between; padding: 10px; background: #f1f1f1; border-bottom: 1px solid #ddd; }
        .product { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .total { font-size: 1.5rem; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
<div class="top-nav">
    <a href="javascript:history.back()" class="btn btn-link">&lt; Back</a>
    <div>
        <?php if ($isSeller): ?>
            <a href="seller.php" class="btn btn-outline-info">Sell a product</a>
        <?php else: ?>
            <a href="seller.php" class="btn btn-outline-primary">Become a seller</a>
        <?php endif; ?>
    </div>
    <div>
        <a href="notification.php" class="btn btn-outline-secondary">🔔</a>
        <a href="leaderboard.php" class="btn btn-warning ml-2">Leader board</a>
        <a href="user.php" class="btn btn-outline-dark ml-2">👤</a>
        <a href="home.php" class="btn btn-secondary ml-2">Go Home</a>
    </div>
</div>

<div class="container my-4">
    <h3>Checkout Summary</h3>
    <?php foreach ($items as $item): ?>
        <div class="product d-flex align-items-center">
            <img src="<?= $item['Image'] ?>" alt="<?= $item['Name'] ?>" style="width: 80px; height: 80px; object-fit: cover;">
            <div class="ml-3">
                <h5><?= $item['Name'] ?></h5>
                <p>Price: <?= $item['Price'] ?> BDT</p>
                <a href="remove_from_cart.php?Product_ID=<?= $item['Product_ID'] ?>" class="btn btn-danger btn-sm">Remove</a>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="total">Total: <?= $total ?> BDT</div>

    <?php if ($confirmationMessage): ?>
        <div class="alert alert-info mt-4"><?= $confirmationMessage ?></div>
    <?php endif; ?>

    <hr>
    <h4>Add a delivery address</h4>
    <form method="POST">
        <div class="form-group">
            <label>Select division</label>
            <select name="division" class="form-control" required>
                <option value="">Select from one</option>
                <option value="Dhaka">Dhaka</option>
                <option value="Chattogram">Chattogram</option>
                <option value="Khulna">Khulna</option>
                <option value="Rajshahi">Rajshahi</option>
                <option value="Barisal">Barisal</option>
                <option value="Sylhet">Sylhet</option>
                <option value="Rangpur">Rangpur</option>
                <option value="Mymensingh">Mymensingh</option>
            </select>
        </div>
        <div class="form-group">
            <label>Select city</label>
            <input type="text" name="city" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Select area</label>
            <input type="text" name="area" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Add a street address</label>
            <input type="text" name="street" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Confirm Order & Pay</button>
        <a href="payment.php" class="btn btn-outline-info ml-2">Add Payment Method</a>
    </form>
</div>
</body>
</html>
