<?php
require 'db.php';

if (!isset($_GET['id'])) {
    echo "No ticket ID provided.";
    exit();
}

$ticket_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT t.ticket_id, v.vehicle_number, vl.violation_name, t.ticket_date, t.issue_date, d.driver_name
    FROM Tickets t
    JOIN Vehicles v ON t.vehicle_id = v.vehicle_id
    JOIN Violations vl ON t.violation_id = vl.violation_id
    JOIN Drivers d ON t.driver_id = d.driver_id
    WHERE t.ticket_id = ?
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Ticket not found.";
    exit();
}

$ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Ticket #<?= $ticket['ticket_id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #e3f2fd, #fce4ec);
            padding: 50px;
            margin: 0;
            color: #333;
        }

        .ticket-container {
            max-width: 750px;
            margin: auto;
            background: #ffffff;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            font-size: 30px;
            color: #0d47a1;
            margin-bottom: 40px;
        }

        .ticket-info {
            font-size: 18px;
            line-height: 1.9;
        }

        .ticket-info p {
            margin-bottom: 15px;
        }

        .ticket-info strong {
            display: inline-block;
            width: 180px;
            font-weight: 600;
            color: #0d47a1;
        }

        .button-group {
            text-align: center;
            margin-top: 40px;
        }

        .print-button, .back-button {
            display: inline-block;
            background-color: #0d47a1;
            color: #fff;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s ease;
            margin: 0 10px;
        }

        .print-button:hover, .back-button:hover {
            background-color: #08306b;
        }

        @media print {
            .print-button, .back-button {
                display: none;
            }

            body {
                background: white;
                padding: 0;
            }

            .ticket-container {
                box-shadow: none;
                border: none;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="ticket-container">
    <h1>🚔 Traffic Violation Ticket</h1>

    <div class="ticket-info">
        <p><strong>Ticket ID:</strong> <?= $ticket['ticket_id'] ?></p>
        <p><strong>Driver Name:</strong> <?= htmlspecialchars($ticket['driver_name']) ?></p>
        <p><strong>Vehicle Number:</strong> <?= htmlspecialchars($ticket['vehicle_number']) ?></p>
        <p><strong>Violation:</strong> <?= htmlspecialchars($ticket['violation_name']) ?></p>
        <p><strong>Ticket Date:</strong> <?= $ticket['ticket_date'] ?></p>
        <p><strong>Issue Date:</strong> <?= $ticket['issue_date'] ?></p>
    </div>

    <div class="button-group">
        <button class="print-button" onclick="window.print()">🖨️ Print</button>
        <a class="back-button" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
