<?php
session_start();
if (!isset($_SESSION["email"])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "CSE370";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for search and filter
$searchTerm = "";
$filter = "";
$categoryFilter = "";
$orderBy = "";

// Process search form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
}

// Process dropdown filter
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    $filter = $_POST['filter'];
    
    if ($filter == "high-to-low") {
        $orderBy = "ORDER BY Price DESC";
    } elseif ($filter == "low-to-high") {
        $orderBy = "ORDER BY Price ASC";
    } elseif ($filter == "Laptop" || $filter == "Mobile") {
        $categoryFilter = "WHERE Category = '$filter'";
    }
}

// Build the SQL query
$sql = "SELECT * FROM Products";
$conditions = [];

if (!empty($searchTerm)) {
    $conditions[] = "Name LIKE '%$searchTerm%'";
}

if (!empty($categoryFilter)) {
    $conditions[] = $categoryFilter;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

if (!empty($orderBy)) {
    $sql .= " $orderBy";
}

$result = $conn->query($sql);

// Check if user is a verified seller
$isVerifiedSeller = false;
$user_id = $_SESSION['user_id'] ?? 0; // Assuming user_id is stored in session

if ($user_id) {
    $sellerCheck = $conn->query("SELECT NID, Photo_with_NID FROM User_is_buyer_and_seller WHERE User_ID = $user_id");
    if ($sellerCheck->num_rows > 0) {
        $sellerData = $sellerCheck->fetch_assoc();
        if (!is_null($sellerData['NID']) && !is_null($sellerData['Photo_with_NID'])) {
            $isVerifiedSeller = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mr. Thrift - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        /* Section 1 Styles */
        .section1 {
            background-color: #FFD580; /* Light orange */
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .left-buttons {
            display: flex;
            gap: 10px;
        }
        
        .right-buttons {
            display: flex;
            gap: 10px;
        }
        
        button {
            padding: 8px 15px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background-color: #f8f8f8;
        }
        
        button:hover {
            background-color: #e0e0e0;
        }
        
        .brand-name {
            font-size: 36px;
            font-weight: bold;
            margin-top: 20px;
        }
        
        /* Section 2 Styles */
        .section2 {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        
        .search-container {
            display: flex;
            gap: 10px;
        }
        
        #sr1 {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Section 3 Styles */
        .section3 {
            padding: 15px;
        }
        
        .scrollable-box {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            overflow-y: auto;
            max-height: 600px;
            padding: 10px;
        }
        
        .product-row {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: 20px;
        }
        
        .product-box {
            width: calc(33.33% - 20px);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: transform 0.2s;
            box-sizing: border-box;
        }
        
        .product-box:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .product-category {
            color: #666;
        }
        
        .product-price {
            font-weight: bold;
            color: #B12704;
        }
    </style>
</head>
<body>
    <!-- Section 1 -->
    <div class="section1">
        <div class="top-bar">
            <div class="left-buttons">
                <button id="b1" onclick="handleSellerButton()">Seller</button>
                <button id="b2">Notification</button>
                <button id="b3" onclick="window.location.href='mycart.php'">My cart</button>
            </div>
            <div class="right-buttons">
                <button id="b4" onclick="window.location.href='leaderboard.php'">Leader board</button>
                <button id="b5" onclick="window.location.href='user.php'">User</button>
            </div>
        </div>
        <div class="brand-name">Mr. Thrift</div>
    </div>
    
    <!-- Section 2 -->
    <div class="section2">
        <form method="post" class="search-container">
            <input type="text" id="sr1" name="searchTerm" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" id="b6" name="search">Search</button>
        </form>
        <form method="post">
            <select id="dp1" name="filter" onchange="this.form.submit()">
                <option value="">Filter by</option>
                <option value="high-to-low" <?php if($filter == 'high-to-low') echo 'selected'; ?>>Price: high-to-low</option>
                <option value="low-to-high" <?php if($filter == 'low-to-high') echo 'selected'; ?>>Price: low-to-high</option>
                <option value="Laptop" <?php if($filter == 'Laptop') echo 'selected'; ?>>Laptop</option>
                <option value="Mobile" <?php if($filter == 'Mobile') echo 'selected'; ?>>Mobile</option>
            </select>
        </form>
    </div>
    
    <!-- Section 3 -->
    <div class="section3">
        <div class="scrollable-box">
            <?php
            if ($result->num_rows > 0) {
                echo '<div class="product-row">';
                $count = 0;
                
                while($row = $result->fetch_assoc()) {
                    if ($count > 0 && $count % 3 == 0) {
                        echo '</div><div class="product-row">';
                    }
                    ?>
                    <div class="product-box" onclick="window.location.href='product.php?product_id=<?php echo $row['Product_ID']; ?>'">
                        <img src="<?php echo htmlspecialchars($row['Image']); ?>" alt="<?php echo htmlspecialchars($row['Name']); ?>" class="product-image">
                        <div class="product-name"><?php echo htmlspecialchars($row['Name']); ?></div>
                        <div class="product-footer">
                            <span class="product-category"><?php echo htmlspecialchars($row['Category']); ?></span>
                            <span class="product-price">$<?php echo htmlspecialchars($row['Price']); ?></span>
                        </div>
                    </div>
                    <?php
                    $count++;
                }
                echo '</div>';
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>
    
    <script>
        function handleSellerButton() {
            <?php if ($isVerifiedSeller): ?>
                window.location.href = 'sell.php';
            <?php else: ?>
                window.location.href = 'user.php';
            <?php endif; ?>
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>