<?php
session_start();
require 'db.php';
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

// Get real-time ticket count
$ticket_count = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM Tickets");
if ($row = $result->fetch_assoc()) {
    $ticket_count = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(to right, #e0f7fa, #e3f2fd);
            color: #333;
        }
        .sidebar {
            width: 260px;
            background: #0d47a1;
            color: #fff;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            font-size: 26px;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
            color: #ffffff;
        }
        .sidebar a {
            display: block;
            text-decoration: none;
            color: #ffffff;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .main {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .main h1 {
            font-size: 34px;
            margin-bottom: 30px;
            color: #0d47a1;
        }
        .dashboard-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }
        .dashboard-card:hover {
            transform: scale(1.02);
        }
        .dashboard-card h3 {
            font-size: 22px;
            color: #007BFF;
            margin-bottom: 10px;
        }
        .dashboard-card p {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <h2>🚔 Admin Panel</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="issue_ticket.php">🎫 Issue Ticket</a>
        <a href="view_ticket.php">📄 View Tickets</a>
    </div>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Welcome, Admin 👋</h1>
    <div class="dashboard-card">
        <h3>Total Tickets Issued</h3>
        <p><?= $ticket_count ?></p>
    </div>
</div>

</body>
</html>
