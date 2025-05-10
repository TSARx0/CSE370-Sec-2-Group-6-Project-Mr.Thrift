<?php
session_start();
if (!isset($_SESSION["email"])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Get Product_ID from URL
if (!isset($_GET['product_id'])) {
    header("Location: home.php");
    exit();
}
$product_id = intval($_GET['product_id']);

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

// Get product details
$product = [];
$productQuery = $conn->query("SELECT * FROM Products WHERE Product_ID = $product_id");
if ($productQuery->num_rows > 0) {
    $product = $productQuery->fetch_assoc();
} else {
    header("Location: home.php");
    exit();
}

// Get review details
$review = ['Likes' => 0, 'Dislike' => 0, 'Rating' => 0];
$reviewQuery = $conn->query("SELECT * FROM Review WHERE Product_ID = $product_id");
if ($reviewQuery->num_rows > 0) {
    $review = $reviewQuery->fetch_assoc();
}

// Get comments
$comments = [];
$commentQuery = $conn->query("SELECT c.*, u.Name, u.Image FROM Comment c JOIN Users u ON c.User_ID = u.User_ID WHERE c.Product_ID = $product_id ORDER BY c.Date DESC");

if ($commentQuery->num_rows > 0) {
    while($row = $commentQuery->fetch_assoc()) {
        $comments[] = $row;
    }
}

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

// Handle like/dislike/rating actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['like'])) {
        $conn->query("UPDATE Review SET Likes = Likes + 1 WHERE Product_ID = $product_id");
        $review['Likes']++;
    } elseif (isset($_POST['dislike'])) {
        $conn->query("UPDATE Review SET Dislike = Dislike + 1 WHERE Product_ID = $product_id");
        $review['Dislike']++;
    } elseif (isset($_POST['rating'])) {
        $conn->query("UPDATE Review SET Rating = Rating + 1 WHERE Product_ID = $product_id");
        $review['Rating']++;
    } elseif (isset($_POST['add_to_cart'])) {
        // Check if product already in cart
        $checkCart = $conn->query("SELECT * FROM Cart WHERE User_ID = $user_id AND Product_ID = $product_id");
        if ($checkCart->num_rows == 0) {
            $conn->query("INSERT INTO Cart (User_ID, Product_ID) VALUES ($user_id, $product_id)");
        }
    } elseif (isset($_POST['comment_text'])) {
        $comment_text = $conn->real_escape_string($_POST['comment_text']);
        $current_date = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO Comment (Date, Comment, User_ID, Product_ID) VALUES ('$current_date', '$comment_text', $user_id, $product_id)");
        // Refresh comments
        header("Location: product.php?product_id=$product_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Name']); ?> - Mr. Thrift</title>
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
            justify-content: space-between;
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
        
        /* Section 2 Styles */
        .section2 {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        /* Section 3 Styles */
        .section3 {
            padding: 20px;
            text-align: center;
        }
        
        .product-image {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }
        
        /* Section 4 Styles */
        .section4 {
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .product-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 20px;
            color: #B12704;
            margin-bottom: 15px;
        }
        
        .review-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        /* Section 5 Styles */
        .section5 {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .action-buttons button {
            padding: 10px 25px;
            font-size: 16px;
        }
        
        .buy-button {
            background-color: #FFD814;
        }
        
        .cart-button {
            background-color: #FFA41C;
        }
        
        /* Section 6 Styles */
        .section6 {
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .about-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        /* Section 7 Styles */
        .section7 {
            padding: 20px;
        }
        
        .comment-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        #ipt1 {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .comments-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .comment {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .comment:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .comment-user-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .comment-user-name {
            font-weight: bold;
        }
        
        .comment-date {
            color: #666;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .comment-text {
            margin-left: 50px;
        }
    </style>
</head>
<body>
    <!-- Section 1 -->
    <div class="section1">
        <button id="b1" onclick="handleSellerButton()">Seller</button>
        <div>
            <button id="b2">Notification</button>
            <button id="b3" onclick="window.location.href='mycart.php'">My cart</button>
            <button id="b4" onclick="window.location.href='leaderboard.php'">Leaderboard</button>
            <button id="b5" onclick="window.location.href='user.php'">User</button>
        </div>
    </div>
    
    <!-- Section 2 -->
    <div class="section2">
        <button id="b6" onclick="window.location.href='home.php'">Home</button>
    </div>
    
    <!-- Section 3 -->
    <div class="section3">
        <img src="<?php echo htmlspecialchars($product['Image']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>" class="product-image">
    </div>
    
    <!-- Section 4 -->
    <div class="section4">
        <div class="product-name"><?php echo htmlspecialchars($product['Name']); ?></div>
        <div class="product-price">$<?php echo htmlspecialchars($product['Price']); ?></div>
        
        <form method="post" class="review-buttons">
            <button type="submit" id="b7" name="like">Like (<?php echo $review['Likes']; ?>)</button>
            <button type="submit" id="b8" name="dislike">Dislike (<?php echo $review['Dislike']; ?>)</button>
            <button type="submit" id="b9" name="rating">Rating (<?php echo $review['Rating']; ?>)</button>
        </form>
    </div>
    
    <!-- Section 5 -->
    <div class="section5">
        <div class="action-buttons">
            <button id="b10" class="buy-button" onclick="window.location.href='productbuy.php?product_id=<?php echo $product_id; ?>'">Buy</button>
            <form method="post">
                <button type="submit" id="b11" class="cart-button" name="add_to_cart">Add to cart</button>
            </form>
        </div>
    </div>
    
    <!-- Section 6 -->
    <div class="section6">
        <div class="section-title">About</div>
        <div class="about-box">
            <?php echo nl2br(htmlspecialchars($product['About'])); ?>
        </div>
    </div>
    
    <!-- Section 7 -->
    <div class="section7">
        <div class="section-title">Comment</div>
        
        <form method="post" class="comment-input">
            <input type="text" id="ipt1" name="comment_text" placeholder="Write your comment..." required>
            <button type="submit" id="b12">Send message</button>
        </form>
        
        <div class="comments-box">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <img src="<?php echo htmlspecialchars($comment['Image']); ?>" alt="<?php echo htmlspecialchars($comment['Name']); ?>" class="comment-user-image">
                            <span class="comment-user-name"><?php echo htmlspecialchars($comment['Name']); ?></span>
                            <span class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['Date'])); ?></span>
                        </div>
                        <div class="comment-text">
                            <?php echo nl2br(htmlspecialchars($comment['Comment'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
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