<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cse370";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = "Guest";
$email = "";
$password_db = "";
$location = "";
$is_seller = false;

if ($user_id) {
    $stmt = $conn->prepare("SELECT Name, Email, Password, Location FROM Users WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fetched_name, $email, $password_db, $location);
    if ($stmt->fetch()) {
        $user_name = $fetched_name;
    }
    $stmt->close();

    // Check if user is a seller
    $checkSeller = $conn->prepare("SELECT * FROM Seller WHERE User_ID = ?");
    $checkSeller->bind_param("i", $user_id);
    $checkSeller->execute();
    $checkSeller->store_result();
    $is_seller = $checkSeller->num_rows > 0;
    $checkSeller->close();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_location = $_POST['location'];

    $stmt = $conn->prepare("UPDATE Users SET Email = ?, Password = ?, Location = ? WHERE User_ID = ?");
    $stmt->bind_param("sssi", $new_email, $new_password, $new_location, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: userr.php");
    exit();
}

// Handle become seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['become_seller'])) {
    $nid = $_POST['nid'];

    // Add NID column if not exists (optional defensive code)
    $conn->query("ALTER TABLE Seller ADD COLUMN IF NOT EXISTS NID VARCHAR(50)");

    $stmt = $conn->prepare("INSERT INTO Seller (User_ID, NID, Delivery_division, Delivery_city, Delivery_street_address, Date)
                            VALUES (?, ?, NULL, NULL, NULL, NULL)");
    $stmt->bind_param("is", $user_id, $nid);
    $stmt->execute();
    $stmt->close();

    header("Location: userr.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #fceabb, #f8b500);
            margin: 0;
            padding: 0;
        }
        .top-left {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .top-left a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #ffd699;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
        }
        .container {
            margin: 100px auto;
            padding: 20px;
            width: 400px;
            background-color: #fff5cc;
            border-radius: 10px;
            box-shadow: 3px 3px 12px rgba(0,0,0,0.2);
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 15px;
            background-color: #f8b500;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            margin-top: 10px;
        }
        .password-box {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 35%;
            right: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="top-left">
    <a href="home.php">Home</a>
    <a href="leaderboard.php">Leaderboard</a>
</div>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Password:</label>
        <div class="password-box">
            <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password_db); ?>" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>

        <label>Address:</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" required>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <hr>

    <h3>Seller Status:</h3>
    <?php if ($is_seller): ?>
        <p>You are already a seller.</p>
    <?php else: ?>
        <p>Not a seller yet.</p>
        <form method="post">
            <label>Enter NID to become a seller:</label>
            <input type="text" name="nid" required>
            <button type="submit" name="become_seller">Become a Seller</button>
        </form>
    <?php endif; ?>
</div>

<script>
function togglePassword() {
    var input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
