<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Issue Ticket
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['issue_ticket'])) {
    $vehicle_id = $_POST["vehicle_id"];
    $violation_id = $_POST["violation_id"];
    $driver_id = $_POST["driver_id"];
    $ticket_date = $_POST["ticket_date"];
    $issue_date = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO Tickets (vehicle_id, violation_id, driver_id, ticket_date, issue_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $vehicle_id, $violation_id, $driver_id, $ticket_date, $issue_date);
    $message = $stmt->execute() ?
        "<p class='success'>✅ Ticket issued successfully.</p>" :
        "<p class='error'>❌ Failed to issue ticket.</p>";
}

// Delete Ticket
if (isset($_GET['delete_ticket_id'])) {
    $ticket_id = $_GET['delete_ticket_id'];
    $stmt = $conn->prepare("DELETE FROM Tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $message = $stmt->execute() ?
        "<p class='success'>✅ Ticket deleted successfully.</p>" :
        "<p class='error'>❌ Failed to delete ticket.</p>";
}

// Delete All Tickets by Vehicle
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_by_vehicle'])) {
    $vehicle_id = $_POST["delete_vehicle_id"];
    $stmt = $conn->prepare("DELETE FROM Tickets WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $message = $stmt->execute() ?
        "<p class='success'>✅ All tickets for vehicle deleted.</p>" :
        "<p class='error'>❌ Failed to delete tickets.</p>";
}

// Update Tickets by Vehicle
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_by_vehicle'])) {
    $vehicle_id = $_POST["update_vehicle_id"];
    $new_violation_id = $_POST["new_violation_id"];
    $new_driver_id = $_POST["new_driver_id"];
    $stmt = $conn->prepare("UPDATE Tickets SET violation_id = ?, driver_id = ? WHERE vehicle_id = ?");
    $stmt->bind_param("iii", $new_violation_id, $new_driver_id, $vehicle_id);
    $message = $stmt->execute() ?
        "<p class='success'>✅ Tickets updated successfully.</p>" :
        "<p class='error'>❌ Failed to update tickets.</p>";
}

// Dropdown Data
$vehicles = $conn->query("SELECT vehicle_id, vehicle_number FROM Vehicles");
$violations = $conn->query("SELECT violation_id, violation_name FROM Violations");
$drivers = $conn->query("SELECT driver_id, driver_name FROM Drivers");

// Ticket List
$tickets = $conn->query("SELECT t.ticket_id, v.vehicle_number, vi.violation_name, d.driver_name, t.ticket_date, t.issue_date 
                         FROM Tickets t 
                         JOIN Vehicles v ON t.vehicle_id = v.vehicle_id 
                         JOIN Violations vi ON t.violation_id = vi.violation_id 
                         JOIN Drivers d ON t.driver_id = d.driver_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🚦 Issue Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #c3e4ff, #e0f7fa);
            padding: 20px 10px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h2, h3 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
        }

        form {
            background: #f5faff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        label, select, input, button {
            width: 100%;
            margin-top: 10px;
            font-size: 15px;
        }

        select, input[type="date"], button {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
        }

        button {
            background: #007bff;
            color: #fff;
            font-weight: 600;
            border: none;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .success, .error {
            text-align: center;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
        }

        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 14px;
        }

        th {
            background: #004085;
            color: white;
        }

        td {
            background: #f9f9f9;
        }

        a {
            color: #c82333;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .nav {
            text-align: center;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                background: #007bff;
                color: white;
            }

            tr {
                margin-bottom: 15px;
            }

            td {
                padding-left: 50%;
                position: relative;
            }

            td::before {
                position: absolute;
                left: 10px;
                top: 10px;
                font-weight: bold;
                color: #333;
                white-space: nowrap;
            }

            td:nth-of-type(1)::before { content: "ID"; }
            td:nth-of-type(2)::before { content: "Vehicle"; }
            td:nth-of-type(3)::before { content: "Violation"; }
            td:nth-of-type(4)::before { content: "Driver"; }
            td:nth-of-type(5)::before { content: "Ticket Date"; }
            td:nth-of-type(6)::before { content: "Issue Date"; }
            td:nth-of-type(7)::before { content: "Action"; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🚦 Ticket Management Portal</h2>
    <?= $message ?>

    <!-- Issue New Ticket -->
    <form method="POST">
        <h3>✅ Issue New Ticket</h3>
        <label>Vehicle:</label>
        <select name="vehicle_id" required>
            <option value="">-- Select Vehicle --</option>
            <?php $vehicles->data_seek(0); while ($v = $vehicles->fetch_assoc()): ?>
                <option value="<?= $v['vehicle_id'] ?>"><?= htmlspecialchars($v['vehicle_number']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Violation:</label>
        <select name="violation_id" required>
            <option value="">-- Select Violation --</option>
            <?php $violations->data_seek(0); while ($vi = $violations->fetch_assoc()): ?>
                <option value="<?= $vi['violation_id'] ?>"><?= htmlspecialchars($vi['violation_name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Driver:</label>
        <select name="driver_id" required>
            <option value="">-- Select Driver --</option>
            <?php $drivers->data_seek(0); while ($d = $drivers->fetch_assoc()): ?>
                <option value="<?= $d['driver_id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Ticket Date:</label>
        <input type="date" name="ticket_date" max="<?= date('Y-m-d') ?>" required>

        <button type="submit" name="issue_ticket">Issue Ticket</button>
    </form>

    <!-- Tickets Table -->
    <h3>📄 Existing Tickets</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Violation</th>
                <th>Driver</th>
                <th>Ticket Date</th>
                <th>Issue Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($ticket = $tickets->fetch_assoc()): ?>
            <tr>
                <td><?= $ticket['ticket_id'] ?></td>
                <td><?= htmlspecialchars($ticket['vehicle_number']) ?></td>
                <td><?= htmlspecialchars($ticket['violation_name']) ?></td>
                <td><?= htmlspecialchars($ticket['driver_name']) ?></td>
                <td><?= $ticket['ticket_date'] ?></td>
                <td><?= $ticket['issue_date'] ?></td>
                <td><a href="?delete_ticket_id=<?= $ticket['ticket_id'] ?>" onclick="return confirm('Delete this ticket?')">❌ Delete</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Delete Tickets -->
    <form method="POST">
        <h3>❌ Delete Tickets by Vehicle</h3>
        <label>Select Vehicle:</label>
        <select name="delete_vehicle_id" required>
            <option value="">-- Select Vehicle --</option>
            <?php $vehicles->data_seek(0); while ($v = $vehicles->fetch_assoc()): ?>
                <option value="<?= $v['vehicle_id'] ?>"><?= htmlspecialchars($v['vehicle_number']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="delete_by_vehicle">Delete All Tickets</button>
    </form>

    <!-- Update Tickets -->
    <form method="POST">
        <h3>✏️ Update Tickets by Vehicle</h3>
        <label>Select Vehicle:</label>
        <select name="update_vehicle_id" required>
            <option value="">-- Select Vehicle --</option>
            <?php $vehicles->data_seek(0); while ($v = $vehicles->fetch_assoc()): ?>
                <option value="<?= $v['vehicle_id'] ?>"><?= htmlspecialchars($v['vehicle_number']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>New Violation:</label>
        <select name="new_violation_id" required>
            <option value="">-- Select Violation --</option>
            <?php $violations->data_seek(0); while ($vi = $violations->fetch_assoc()): ?>
                <option value="<?= $vi['violation_id'] ?>"><?= htmlspecialchars($vi['violation_name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>New Driver:</label>
        <select name="new_driver_id" required>
            <option value="">-- Select Driver --</option>
            <?php $drivers->data_seek(0); while ($d = $drivers->fetch_assoc()): ?>
                <option value="<?= $d['driver_id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="update_by_vehicle">Update Tickets</button>
    </form>

    <div class="nav">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
