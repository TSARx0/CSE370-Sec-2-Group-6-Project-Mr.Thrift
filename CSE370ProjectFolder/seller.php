<?php
session_start();
$User_ID = $_SESSION['User_ID'] ?? 1; // For testing, default to 1

// DB connection
$host = "localhost";
$username = "root";
$password = "";
$database = "cse370";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $division = $_POST['division'];
    $city = $_POST['city'];
    $street = $_POST['street'];
    $about = $_POST['about'] ?? '';
    $imagePath = '';

    // Image upload handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $imagePath = $uploadDir . time() . "_" . $imageName;
        move_uploaded_file($imageTmp, $imagePath);
    }

    // Insert into Products
    $stmt = $conn->prepare("INSERT INTO Products (Name, Location, About, Price, Category, Image) VALUES (?, ?, ?, ?, ?, ?)");
    $location = "$division, $city";
    $stmt->bind_param("sssiss", $name, $location, $about, $price, $category, $imagePath);
    $stmt->execute();
    $productID = $stmt->insert_id;

    // Insert into Seller
    $stmt2 = $conn->prepare("INSERT INTO Seller (Delivery_division, Delivery_city, Delivery_street_address, Date, User_ID, Product_ID) VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt2->bind_param("sssii", $division, $city, $street, $User_ID, $productID);
    $stmt2->execute();

    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell a Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .top-nav {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
        }
        .form-section {
            padding: 20px;
        }
        .pickup-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-top: 20px;
        }
        label.required::after {
            content: "*";
            color: red;
            margin-left: 3px;
        }
    </style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
    <div></div>
    <div>
        <a href="notification.php" class="btn btn-outline-secondary">🔔</a>
        <a href="mycart.php" class="btn btn-outline-primary ml-2">🛒</a>
        <a href="leaderboard.php" class="btn btn-warning ml-2">Leader board</a>
        <a href="user.php" class="btn btn-outline-dark ml-2">👤<br><small>User</small></a>
    </div>
</div>

<!-- Page Header -->
<div class="container d-flex justify-content-between align-items-center my-3">
    <a href="javascript:history.back()" class="btn btn-link">&lt; Back</a>
    <h2 class="text-center flex-grow-1">Sell a product</h2>
    <a href="home.php" class="btn btn-secondary">Go Home</a>
</div>

<!-- Form Section -->
<div class="container form-section">
    <form method="POST" enctype="multipart/form-data">
        <!-- Upload Image -->
        <div class="form-group">
            <label class="required">Add pictures of your product</label>
            <input type="file" name="image" class="form-control-file" required>
        </div>

        <!-- Product Info -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="required">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label class="required">Set price</label>
                <input type="number" name="price" class="form-control" required>
            </div>
        </div>

        <!-- Category -->
        <div class="form-group">
            <label class="required">Select product category</label>
            <select name="category" class="form-control" required>
                <option value="">Select product category</option>
                <option value="Laptop">Laptop</option>
                <option value="Mobile">Mobile</option>
            </select>
        </div>

        <!-- About Product -->
        <div class="form-group">
            <label for="about">About Product</label>
            <textarea name="about" id="about" class="form-control" rows="4" placeholder="Write something about the product..."></textarea>
        </div>

        <!-- Pickup Address -->
        <div class="pickup-box">
            <h5 class="mb-3">Add a product pickup address</h5>

            <div class="form-group">
                <label class="required">Select division</label>
                <select name="division" class="form-control" required>
                    <option value="">Select from one</option>
                    <option>Dhaka</option>
                    <option>Chattogram</option>
                    <option>Khulna</option>
                    <option>Rajshahi</option>
                    <option>Barisal</option>
                    <option>Sylhet</option>
                    <option>Rangpur</option>
                    <option>Mymensingh</option>
                </select>
            </div>

            <div class="form-group">
                <label class="required">Select city</label>
                <select name="city" class="form-control" required>
                    <option value="">Select from one</option>
                    <option>Dhaka</option>
                    <option>Chattogram</option>
                    <option>Khulna</option>
                    <option>Rajshahi</option>
                    <option>Barisal</option>
                    <option>Sylhet</option>
                    <option>Rangpur</option>
                    <option>Mymensingh</option>
                </select>
            </div>

            <div class="form-group">
                <label class="required">Add a street address</label>
                <input type="text" name="street" class="form-control" required>
            </div>
        </div>

        <!-- Buttons -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success mb-2">Done</button><br>
            <button type="submit" class="btn btn-lg btn-primary btn-block">Publish product for sell</button>
        </div>
    </form>
</div>

</body>
</html>
