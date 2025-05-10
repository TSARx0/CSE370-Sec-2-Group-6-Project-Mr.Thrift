<?php
session_start();
if (!isset($_SESSION["email"])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "CSE370";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get user points sorted in descending order
$sql = "SELECT User_ID, Point FROM User_point ORDER BY Point DESC";
$result = $conn->query($sql);

$leaderboard = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .section1 {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        .left-buttons, .right-buttons {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .section2 {
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .section3 {
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            max-width: 600px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Section 1: Navigation buttons -->
    <div class="section1">
        <div class="left-buttons">
            <button id="b1" onclick="window.location.href='home.php'">Home</button>
        </div>
        <div class="right-buttons">
            <button id="b2" onclick="window.location.href='mycart.php'">My cart</button>
            <button id="b3">Notification</button>
            <button id="b4" onclick="window.location.href='user.php'">User</button>
        </div>
    </div>

    <!-- Section 2: Title -->
    <div class="section2">
        Leader board
    </div>

    <!-- Section 3: Leaderboard table -->
    <div class="section3">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                $display_count = min(10, count($leaderboard)); // Show up to 10 rows plus header
                for ($i = 0; $i < $display_count; $i++) {
                    echo "<tr>";
                    echo "<td>" . $leaderboard[$i]['User_ID'] . "</td>";
                    echo "<td>" . $rank . "</td>";
                    echo "</tr>";
                    $rank++;
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>