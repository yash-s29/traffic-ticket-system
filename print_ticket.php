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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family:'Inter',sans-serif; transition: background 0.3s, color 0.3s; }
body.light { background: linear-gradient(to right,#e0f2fe,#f0f9ff); color: #1e3a8a; }
body.dark { background:#1f2937; color:#f5f5f5; }

.ticket-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 50px 40px;
    border-radius: 20px;
    background: inherit;
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    border-top: 6px solid #1e40af;
    animation: fadeIn 0.5s ease-in-out;
}

h1 {
    text-align:center;
    font-size:32px;
    font-weight:700;
    margin-bottom:40px;
    color: #1e40af;
}
body.dark h1 { color: #60a5fa; }

.ticket-info {
    font-size: 18px;
    line-height: 2;
}
.ticket-info p {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
.ticket-info strong {
    font-weight:600;
    color:#2563eb;
    width: 200px;
}
body.dark .ticket-info strong { color:#60a5fa; }

.button-group {
    text-align: center;
    margin-top: 40px;
}
.print-button, .back-button {
    display:inline-block;
    background: linear-gradient(to right,#2563eb,#1e40af);
    color:#fff;
    padding:14px 36px;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    border:none;
    cursor:pointer;
    text-decoration:none;
    margin:0 10px;
    transition:0.3s ease, transform 0.2s;
}
.print-button:hover, .back-button:hover { background: linear-gradient(to right,#1e40af,#0c2461); transform: translateY(-2px); }

@media print {
    body { background: #fff; padding:0; color:#000; }
    .ticket-container { box-shadow:none; border:none; padding:30px; border-radius:0; border-top:4px solid #000; }
    .button-group { display:none; }
}

@keyframes fadeIn {
    from { opacity:0; transform:translateY(20px); }
    to { opacity:1; transform:translateY(0); }
}

@media (max-width: 600px) {
    .ticket-info p { flex-direction: column; }
    .ticket-info strong { width:100%; margin-bottom:5px; }
}
</style>
</head>
<body>

<div class="ticket-container">
    <h1>🚔 Traffic Violation Ticket</h1>

    <div class="ticket-info">
        <p><strong>Ticket ID:</strong> <span><?= $ticket['ticket_id'] ?></span></p>
        <p><strong>Driver Name:</strong> <span><?= htmlspecialchars($ticket['driver_name']) ?></span></p>
        <p><strong>Vehicle Number:</strong> <span><?= htmlspecialchars($ticket['vehicle_number']) ?></span></p>
        <p><strong>Violation:</strong> <span><?= htmlspecialchars($ticket['violation_name']) ?></span></p>
        <p><strong>Ticket Date:</strong> <span><?= $ticket['ticket_date'] ?></span></p>
        <p><strong>Issue Date:</strong> <span><?= $ticket['issue_date'] ?></span></p>
    </div>

    <div class="button-group">
        <button class="print-button" onclick="window.print()">🖨️ Print</button>
        <a class="back-button" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
const savedTheme = localStorage.getItem('theme') || 'light';
document.body.classList.add(savedTheme);
</script>
</body>
</html>
