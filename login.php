<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM Admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🚦 Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-light: #3b82f6;
    --primary-dark: #06b6d4;
    --text-light: #1e3a8a;
    --text-dark: #f5f5f5;
    --error-bg: rgba(248, 113, 113, 0.15);
    --error-color: #b91c1c;
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; transition: all 0.3s ease; }
body.light { background: linear-gradient(145deg,#e0f7fa,#ffffff); color: var(--text-light); }
body.dark { background: linear-gradient(145deg,#0f172a,#1e293b); color: var(--text-dark); }
body { min-height: 100vh; display: flex; justify-content: center; align-items: center; }

.login-container {
    width: 100%;
    max-width: 420px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px) saturate(180%);
    -webkit-backdrop-filter: blur(12px) saturate(180%);
    border-radius: 25px;
    padding: 50px 35px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    text-align: center;
    animation: fadeInUp 0.8s ease forwards;
}
body.dark .login-container {
    background: rgba(25,25,25,0.5);
    box-shadow: 0 15px 40px rgba(0,0,0,0.5);
}

@keyframes fadeInUp { from { opacity:0; transform: translateY(30px);} to {opacity:1; transform: translateY(0);} }

h2 {
    font-weight: 700; font-size: 32px; margin-bottom: 30px;
    background: linear-gradient(90deg,var(--primary-light),var(--primary-dark));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}

form label { display:block; margin-bottom:8px; font-weight:600; font-size:14px; }
input[type="text"], input[type="password"] {
    width: 100%;
    padding: 16px 18px;
    margin-bottom: 20px;
    border-radius: 15px;
    border: none;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(6px);
    box-shadow: inset 4px 4px 6px rgba(0,0,0,0.1), inset -4px -4px 6px rgba(255,255,255,0.5);
    font-size:16px;
    color: inherit;
}
input[type="text"]:focus, input[type="password"]:focus {
    outline:none;
    box-shadow: 0 8px 20px rgba(59,130,246,0.3);
    background: rgba(255,255,255,0.25);
}

button {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 15px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    background: linear-gradient(135deg,var(--primary-light),var(--primary-dark));
    color: #fff;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(59,130,246,0.3);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(59,130,246,0.5);
}

.error {
    margin-top: 15px;
    padding: 14px;
    border-radius: 12px;
    background: var(--error-bg);
    color: var(--error-color);
    font-weight:600;
    font-size:14px;
    backdrop-filter: blur(4px);
}

@media(max-width:480px) {
    .login-container { padding: 40px 25px; }
    h2 { font-size: 26px; }
    input, button { font-size: 15px; padding:14px; }
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

        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>
</div>

<script>
const savedTheme = localStorage.getItem('theme') || 'light';
document.body.classList.add(savedTheme);
</script>
</body>
</html>
