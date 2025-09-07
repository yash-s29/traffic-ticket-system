<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM Admin WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $_SESSION["admin"] = $username;  
        header("Location: dashboard.php");  
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login</title>
<style>
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #ffffff, #e6f0ff); 
        height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #444;
    }
    .login-container {
        background: #fff;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 380px;
        text-align: center;
        animation: fadeIn 0.8s ease forwards;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    h2 {
        margin-bottom: 25px;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: 1px;
    }
    form label {
        display: block;
        text-align: left;
        margin-bottom: 6px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 2px solid #ccd6f6; 
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.4); /* blue glow */
    }
    button {
        width: 100%;
        padding: 14px;
        background-color: #3b82f6; 
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.3s ease;
        letter-spacing: 0.8px;
    }
    button:hover {
        background-color: #2563eb;
    }
    .error {
        color: #e74c3c;
        background-color: #fdecea;
        border: 1px solid #f5c6cb;
        padding: 10px;
        margin-top: 20px;
        border-radius: 8px;
        font-weight: 600;
    }
    @media (max-width: 400px) {
        .login-container {
            padding: 30px 20px;
            max-width: 320px;
        }
        input[type="text"],
        input[type="password"],
        button {
            font-size: 15px;
            padding: 12px;
        }
    }
</style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <form method="post" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required />

        <button type="submit">Login</button>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
