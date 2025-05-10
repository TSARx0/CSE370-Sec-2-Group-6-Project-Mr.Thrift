<?php
// Start session
session_start();

// Define variables
$email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["ipt1"];
    $password = $_POST["ipt2"];

    // Connect to MySQL
    $conn = new mysqli("localhost", "root", "", "CSE370");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize inputs and prepare SQL query
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    $sql = "SELECT * FROM Users WHERE Email='$email' AND Password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        // Successful login
        $_SESSION["email"] = $email;
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid Email or Password.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Mr. Thrift</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }
        #section1 {
            padding: 20px;
            background-color: #fff;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        #section1 button {
            padding: 10px 20px;
            background-color: orange;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        #section1 button:active {
            background-color: darkorange;
        }
        #section2, #section3, #section4, #section5 {
            text-align: center;
            margin: 20px auto;
            width: 100%;
        }
        #section2 h1 {
            font-size: 36px;
            margin: 0;
        }
        #section2 h2 {
            font-size: 24px;
            margin-top: 10px;
            color: #555;
        }
        #section3 label {
            display: block;
            margin: 10px auto 5px;
            font-size: 18px;
        }
        #section3 input {
            padding: 10px;
            width: 250px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        #section4 button {
            padding: 10px 30px;
            font-size: 18px;
            background-color: orange;
            border: none;
            color: white;
            cursor: pointer;
        }
        #section4 button:active {
            background-color: darkorange;
        }
        #section5 a {
            text-decoration: none;
            color: blue;
            font-size: 16px;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- Section 1 -->
<div id="section1">
    <button id="b1" onclick="window.location.href='home.php'">Home</button>
    <button id="b2" onclick="window.location.href='signup.php'">Create an account</button>
</div>

<!-- Section 2 -->
<div id="section2">
    <h1>Mr. Thrift</h1>
    <h2>Login</h2>
</div>

<!-- Section 3 -->
<div id="section3">
    <form method="POST" action="">
        <label for="ipt1">Email</label>
        <input type="email" id="ipt1" name="ipt1" required>
        <label for="ipt2">Password</label>
        <input type="password" id="ipt2" name="ipt2" required>

        <!-- Section 4 -->
        <div id="section4">
            <button type="submit" id="b3">Login</button>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </form>
</div>

<!-- Section 5 -->
<div id="section5">
    <p>Don't have an account? <a href="signup.php">Signup here.</a></p>
</div>

</body>
</html>
