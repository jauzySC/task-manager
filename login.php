<?php
session_start();
require('config.php');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: task.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #222;
        }
        input {
            display: block;
            margin: 10px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type=submit] {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        input[type=submit]:hover {
            background: #0056b3;
        }
        a {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
        }
        a:hover { text-decoration: underline; }
        .error {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>

    <?php
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = getConnection()->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            } else {
                echo "<p class='error'>Invalid password.</p>";
            }
        } else {
            echo "<p class='error'>User not found.</p>";
        }

        $stmt->close();
    }
    ?>

    <form action="" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
