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

function calculateUserRating($conn, $user_id) {
    // Fetch user details
    $stmt = $conn->prepare("
        SELECT 
            Name, Email, Password, Location,
            (Name IS NOT NULL AND Name != '') +
            (Email IS NOT NULL AND Email != '') +
            (Password IS NOT NULL AND Password != '') +
            (Location IS NOT NULL AND Location != '') AS completed_attributes
        FROM Users 
        WHERE User_ID = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If no user found, return base rating
    if ($result->num_rows === 0) {
        return [
            'points' => 0,
            'level' => 0,
            'completed_attributes' => 0,
            'total_attributes' => 4
        ];
    }
    
    // Fetch user details
    $user = $result->fetch_assoc();
    $stmt->close();

    // Calculate points (25 points per completed attribute)
    $completed_attributes = $user['completed_attributes'];
    $points = $completed_attributes * 25;

    // Determine level based on completed attributes
    $level = $completed_attributes;

    return [
        'points' => $points,
        'level' => $level,
        'completed_attributes' => $completed_attributes,
        'total_attributes' => 4
    ];
}

// Get the current user's ID (assuming it's set in the session)
$user_id = $_SESSION['user_id'] ?? null;

// Initialize variables
$user_rating = null;

if ($user_id) {
    // Calculate user rating
    $user_rating = calculateUserRating($conn, $user_id);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Rating</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #fceabb, #f8b500);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff5cc;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .rating-bar {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 13px;
            padding: 3px;
            margin-top: 15px;
        }
        .rating-bar-fill {
            height: 20px;
            background-color: <?php 
                if ($user_rating['level'] <= 1) {
                    echo '#ff4d4d'; // Red for low rating
                } elseif ($user_rating['level'] <= 2) {
                    echo '#ffa500'; // Orange for medium rating
                } elseif ($user_rating['level'] <= 3) {
                    echo '#f8b500'; // Yellow for good rating
                } else {
                    echo '#4CAF50'; // Green for high rating
                }
            ?>;
            width: <?php echo $user_rating ? ($user_rating['points']) : '0'; ?>%;
            border-radius: 10px;
            transition: width 0.5s ease-in-out;
        }
        .rating-details {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
        .level-indicator {
            margin-top: 15px;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }
        .details-section {
            margin-top: 20px;
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Rating</h1>

        <?php if (!$user_rating): ?>
            <p>Unable to retrieve user rating.</p>
        <?php else: ?>
            <div class="rating-bar">
                <div class="rating-bar-fill"></div>
            </div>

            <div class="rating-details">
                <span>Total Points: <?php echo $user_rating['points']; ?>/100</span>
                <span>Level: <?php echo $user_rating['level']; ?>/4</span>
            </div>

            <div class="details-section">
                <h2>Rating Breakdown</h2>
                <div class="detail-row">
                    <span>Name</span>
                    <span><?php echo $user_rating['completed_attributes'] > 0 ? 'Completed (25 pts)' : 'Incomplete (0 pts)'; ?></span>
                </div>
                <div class="detail-row">
                    <span>Email</span>
                    <span><?php echo $user_rating['completed_attributes'] > 1 ? 'Completed (25 pts)' : 'Incomplete (0 pts)'; ?></span>
                </div>
                <div class="detail-row">
                    <span>Password</span>
                    <span><?php echo $user_rating['completed_attributes'] > 2 ? 'Completed (25 pts)' : 'Incomplete (0 pts)'; ?></span>
                </div>
                <div class="detail-row">
                    <span>Location</span>
                    <span><?php echo $user_rating['completed_attributes'] > 3 ? 'Completed (25 pts)' : 'Incomplete (0 pts)'; ?></span>
                </div>
            </div>

            <?php if ($user_rating['level'] < 4): ?>
                <p><strong>Recommendation:</strong> Complete your profile to reach the highest level!</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>