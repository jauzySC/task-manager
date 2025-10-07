<?php
session_start();
require __DIR__ . '/config.php';  // or ../config.php depending on folder
$conn = getConnection();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hashed);

            if ($insert->execute()) {
                // ✅ Auto-login after successful registration
                $_SESSION['user_id'] = $insert->insert_id;
                $_SESSION['username'] = $username;

                header("Location: task.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
            $insert->close();
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - ToDo App</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #222;
        }

        input[type=text],
        input[type=password] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        input[type=submit] {
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            margin-top: 10px;
        }

        input[type=submit]:hover {
            background: #0056b3;
        }

        .error {
            color: #c00;
            background: #fee;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .link {
            margin-top: 15px;
            font-size: 13px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="submit" value="Register">
    </form>

    <div class="link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
</body>
</html>
