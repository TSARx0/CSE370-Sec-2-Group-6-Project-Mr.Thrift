<?php
$servername = "localhost";
$username = "root"; // adjust as needed
$password = "";     // adjust as needed
$dbname = "CSE370";
$errors = [];
$captcha_passed = false;
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // MySQL Connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Form Data
    $user_id = trim($_POST["ipt1"]);
    $name = trim($_POST["ipt2"]);
    $email = trim($_POST["ipt3"]);
    $password1 = $_POST["ipt4"];
    $password2 = $_POST["ipt5"];
    $location = trim($_POST["ipt6"]);
    $agree_terms = isset($_POST["cb2"]);
    $captcha = $_POST["captcha"];
    $expected_captcha = $_POST["expected_captcha"];

    if ($password1 !== $password2) {
        $errors[] = "Passwords do not match.";
    }

    if (!$agree_terms) {
        $errors[] = "You must agree to the terms and conditions.";
    }

    if ($captcha !== $expected_captcha) {
        $errors[] = "Captcha is incorrect.";
    }

    // Check if User ID or Email exists
    $stmt = $conn->prepare("SELECT * FROM Users WHERE User_ID = ? OR Email = ?");
    $stmt->bind_param("is", $user_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "User ID or Email already exists.";
    }

    if (empty($errors)) {
        // File Upload Paths
        $nid_path = NULL;
        $photo_nid_path = NULL;

        if (isset($_POST['cb1'])) {
            if (isset($_FILES["b3"]) && $_FILES["b3"]["error"] === 0) {
                $nid_path = "NID/" . $user_id . "." . pathinfo($_FILES["b3"]["name"], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES["b3"]["tmp_name"], $nid_path);
            }
            if (isset($_FILES["b4"]) && $_FILES["b4"]["error"] === 0) {
                $photo_nid_path = "NID_Photo/" . $user_id . "." . pathinfo($_FILES["b4"]["name"], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES["b4"]["tmp_name"], $photo_nid_path);
            }
        }

        // Insert into Users
        $stmt = $conn->prepare("INSERT INTO Users (User_ID, Email, Name, Password, Location, Image) VALUES (?, ?, ?, ?, ?, NULL)");
        $stmt->bind_param("issss", $user_id, $email, $name, $password1, $location);
        $stmt->execute();

        // Insert into User_point
        $stmt = $conn->prepare("INSERT INTO User_point (User_ID, Point) VALUES (?, 0)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Insert into User_is_buyer_and_seller
        $stmt = $conn->prepare("INSERT INTO User_is_buyer_and_seller (NID, Photo_with_NID, User_ID) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nid_path, $photo_nid_path, $user_id);
        $stmt->execute();

        $success = true;
        header("Location: login.php");
        exit();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup - Mr. Thrift</title>
    <style>
        body { font-family: Arial; text-align: center; }
        #section1 { position: absolute; top: 0; right: 20px; padding: 10px; }
        #b1, #b2 { background-color: orange; color: white; border: none; padding: 10px 20px; margin: 5px; }
        #b1:active, #b2:active { background-color: darkorange; }
        .section { margin-top: 60px; }
        .input-box { display: block; margin: 10px auto; }
        .hidden-box { display: none; margin-top: 10px; text-align: left; width: 300px; margin-left: auto; margin-right: auto; }
        #b3, #b4 { background-color: lightgrey; padding: 5px 10px; border: none; }
        #b3:active, #b4:active { background-color: #aaa; }
        .error { color: red; }
    </style>
    <script>
        function toggleSellerBox() {
            var cb = document.getElementById("cb1");
            var box = document.getElementById("sellerBox");
            box.style.display = cb.checked ? "block" : "none";
        }
    </script>
</head>
<body>
    <div id="section1">
        <button id="b1" onclick="location.href='home.php'">Home</button>
        <button id="b2" onclick="location.href='login.php'">Login</button>
    </div>

    <div class="section" id="section2">
        <h1>Mr. Thrift</h1>
        <h2>Signup</h2>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="section" id="section3">
            <label>User ID: <input type="text" id="ipt1" name="ipt1" class="input-box" required></label>
            <label>Name: <input type="text" id="ipt2" name="ipt2" class="input-box" required></label>
            <label>Email: <input type="email" id="ipt3" name="ipt3" class="input-box" required></label>
            <label>Password: <input type="password" id="ipt4" name="ipt4" class="input-box" required></label>
            <label>Re-enter Password: <input type="password" id="ipt5" name="ipt5" class="input-box" required></label>
            <label>Address: <input type="text" id="ipt6" name="ipt6" class="input-box"></label>
        </div>

        <div class="section" id="section4">
            <label><input type="checkbox" id="cb1" name="cb1" onclick="toggleSellerBox()"> I also want to sell</label>
            <div id="sellerBox" class="hidden-box">
                <label>Submit NID: <input type="file" id="b3" name="b3"></label><br><br>
                <label>Submit a photo of you holding your NID: <input type="file" id="b4" name="b4"></label>
            </div>
        </div>

        <div class="section" id="section5">
            <label><input type="checkbox" id="cb2" name="cb2"> I have read and agree to the terms and conditions of Mr. Thrift</label><br><br>
            <label>Solve Captcha: What is 5 + 3? <input type="text" name="captcha" required></label>
            <input type="hidden" name="expected_captcha" value="8">
            <br><br>
            <button type="submit" id="b5">Signup</button>
        </div>

        <?php
        if (!empty($errors)) {
            echo "<div class='error'><ul>";
            foreach ($errors as $e) echo "<li>$e</li>";
            echo "</ul></div>";
        }
        ?>
    </form>
</body>
</html>
