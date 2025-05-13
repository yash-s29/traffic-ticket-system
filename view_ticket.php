<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$tickets = $conn->query("
    SELECT t.ticket_id, v.vehicle_number, d.driver_name, vl.violation_name, t.ticket_date, t.issue_date
    FROM Tickets t
    JOIN Vehicles v ON t.vehicle_id = v.vehicle_id
    JOIN Violations vl ON t.violation_id = vl.violation_id
    JOIN Drivers d ON t.driver_id = d.driver_id
    ORDER BY t.ticket_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📄 View Issued Tickets</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px 20px;
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 30px;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fafafa;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f9fc;
        }
        tr:hover {
            background-color: #e0f0ff;
            transition: background-color 0.2s ease;
        }
        .print-link a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }
        .print-link a:hover {
            text-decoration: underline;
        }
        .back-btn {
            text-align: center;
            margin-top: 25px;
        }
        .back-btn a {
            text-decoration: none;
            font-weight: bold;
            color: white;
            background: linear-gradient(to right, #007BFF, #0056b3);
            padding: 12px 24px;
            border-radius: 10px;
            display: inline-block;
            transition: background 0.3s ease, transform 0.2s;
        }
        .back-btn a:hover {
            background: linear-gradient(to right, #0056b3, #003d80);
            transform: scale(1.03);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📄 Issued Traffic Tickets</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle Number</th>
                <th>Driver Name</th>
                <th>Violation</th>
                <th>Ticket Date</th>
                <th>Issue Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ticket_id'] ?></td>
                    <td><?= htmlspecialchars($row['vehicle_number']) ?></td>
                    <td><?= htmlspecialchars($row['driver_name']) ?></td>
                    <td><?= htmlspecialchars($row['violation_name']) ?></td>
                    <td><?= $row['ticket_date'] ?></td>
                    <td><?= $row['issue_date'] ?></td>
                    <td class="print-link">
                        <a href="print_ticket.php?id=<?= $row['ticket_id'] ?>" target="_blank">🖨️ Print</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="back-btn">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
