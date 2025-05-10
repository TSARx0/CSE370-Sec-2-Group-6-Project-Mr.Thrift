<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mr. Thrift</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        section {
            margin: 20px 0;
            text-align: center;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 0;
        }

        button {
            padding: 12px 24px;
            font-size: 16px;
            margin: 0 10px;
            border: none;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #b1, #b2 {
            background-color: orange;
        }

        #b3 {
            background-color: darkorange;
        }

        .clicked {
            background-color: blue !important;
        }
    </style>
    <script>
        function handleClick(buttonId, url) {
            const btn = document.getElementById(buttonId);
            btn.classList.add('clicked');
            setTimeout(() => {
                window.location.href = url;
            }, 200); // Slight delay to show the color change
        }
    </script>
</head>
<body>

    <!-- Section 1 -->
    <section id="section1">
        <h1>Mr. Thrift</h1>
    </section>

    <!-- Section 2 -->
    <section id="section2">
        <button id="b1" onclick="handleClick('b1', 'login.php')">Login</button>
        <button id="b2" onclick="handleClick('b2', 'signup.php')">Signup</button>
    </section>

    <!-- Section 3 -->
    <section id="section3">
        <button id="b3" onclick="handleClick('b3', 'home.php')">Go to Homepage</button>
    </section>

</body>
</html>
