<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$vehicles   = $conn->query("SELECT vehicle_id, vehicle_number FROM Vehicles");
$violations = $conn->query("SELECT violation_id, violation_name FROM Violations");
$drivers    = $conn->query("SELECT driver_id, driver_name FROM Drivers");

$violation_emergency_map = [
    'Speeding'               => 'Normal',
    'Running Red Light'      => 'High',
    'Illegal Parking'        => 'Normal',
    'Expired Registration'   => 'High',
    'No Seatbelt'            => 'Critical',
    'Driving Without License'=> 'Critical',
    'Reckless Driving'       => 'Critical',
    'Using Mobile While Driving' => 'High',
    'Expired Insurance'      => 'High',
    'Noise Violation'        => 'Normal'
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['issue_ticket'])) {
    $vehicle_id  = $_POST["vehicle_id"];
    $violation_id= $_POST["violation_id"];
    $driver_id   = $_POST["driver_id"];
    $ticket_date = $_POST["ticket_date"];
    $issue_date  = date("Y-m-d");

    $stmt_v = $conn->prepare("SELECT violation_name FROM Violations WHERE violation_id = ?");
    $stmt_v->bind_param("i", $violation_id);
    $stmt_v->execute();
    $stmt_v->bind_result($violation_name);
    $stmt_v->fetch();
    $stmt_v->close();

    $emergency_level = $violation_emergency_map[$violation_name] ?? 'Normal';

    $stmt = $conn->prepare("INSERT INTO Tickets (vehicle_id, violation_id, driver_id, ticket_date, issue_date, emergency_level) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $vehicle_id, $violation_id, $driver_id, $ticket_date, $issue_date, $emergency_level);
    $message = $stmt->execute()
        ? "<p class='success'>✅ Ticket issued successfully (Emergency: $emergency_level).</p>"
        : "<p class='error'>❌ Failed to issue ticket.</p>";
    $stmt->close();
}

if (isset($_GET['delete_ticket_id'])) {
    $ticket_id = $_GET['delete_ticket_id'];
    $stmt = $conn->prepare("DELETE FROM Tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $message = $stmt->execute()
        ? "<p class='success'>✅ Ticket deleted successfully.</p>"
        : "<p class='error'>❌ Failed to delete ticket.</p>";
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_by_vehicle'])) {
    $vehicle_id      = $_POST["update_vehicle_id"];
    $new_violation_id= $_POST["new_violation_id"];
    $new_driver_id   = $_POST["new_driver_id"];

    $stmt_v = $conn->prepare("SELECT violation_name FROM Violations WHERE violation_id = ?");
    $stmt_v->bind_param("i", $new_violation_id);
    $stmt_v->execute();
    $stmt_v->bind_result($violation_name);
    $stmt_v->fetch();
    $stmt_v->close();

    $new_emergency_level = $violation_emergency_map[$violation_name] ?? 'Normal';

    $stmt = $conn->prepare("UPDATE Tickets SET violation_id=?, driver_id=?, emergency_level=? WHERE vehicle_id=?");
    $stmt->bind_param("issi", $new_violation_id, $new_driver_id, $new_emergency_level, $vehicle_id);
    $message = $stmt->execute()
        ? "<p class='success'>✅ Tickets updated successfully (Emergency: $new_emergency_level).</p>"
        : "<p class='error'>❌ Failed to update tickets.</p>";
    $stmt->close();
}

$tickets = $conn->query("
    SELECT t.ticket_id, v.vehicle_number, vi.violation_name, d.driver_name, t.ticket_date, t.issue_date, t.emergency_level
    FROM Tickets t
    JOIN Vehicles v ON t.vehicle_id = v.vehicle_id
    JOIN Violations vi ON t.violation_id = vi.violation_id
    JOIN Drivers d ON t.driver_id = d.driver_id
    ORDER BY t.ticket_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🚦 Ticket Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary:#3b82f6; --primary-dark:#2563eb;
  --success:#10b981; --error:#ef4444;
  --high:#f97316; --critical:#dc2626;
  --bg-light:#f0f4f8; --bg-dark:#0f1724;
  --card-light:#fff; --card-dark:#1f2937;
  --text-light:#111827; --text-dark:#f5f5f5;
  --radius:16px; --transition:.3s ease;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;transition:var(--transition);}
body.light{background:var(--bg-light);color:var(--text-light);}
body.dark{background:var(--bg-dark);color:var(--text-dark);}
.container{max-width:1300px;margin:auto;padding:40px 20px;display:flex;flex-direction:column;gap:30px;}
h1{text-align:center;font-size:2rem;font-weight:700;margin-bottom:20px;color:var(--primary);}

.success,.error{text-align:center;padding:12px 20px;border-radius:var(--radius);font-weight:600;}
.success{background:#d1fae5;color:var(--success);}
.error{background:#fee2e2;color:var(--error);}

.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:30px;}

.card{background:var(--card-light);border-radius:var(--radius);padding:25px 30px;box-shadow:0 6px 20px rgba(0,0,0,0.1);}
body.dark .card{background:var(--card-dark);box-shadow:0 6px 20px rgba(0,0,0,0.4);}
.card h3{font-weight:600;margin-bottom:20px;font-size:1.3rem;text-align:center;color:var(--primary);}

form label{display:block;font-weight:500;margin-top:15px;}
form select,form input[type="date"],form button{
 width:100%;padding:12px;margin-top:6px;border-radius:var(--radius);
 border:1px solid rgba(0,0,0,0.1);font-size:1rem;background:#f9fafb;color:inherit;
}
body.dark form select,body.dark form input[type="date"]{background:#374151;border-color:#4b5563;}
form button{background:var(--primary);color:#fff;font-weight:600;border:none;cursor:pointer;margin-top:20px;}
form button:hover{background:var(--primary-dark);transform:scale(1.03);}

.table-card{width:100%;overflow-x:auto;}
.table-card table{width:100%;border-collapse:collapse;margin-top:15px;border-radius:var(--radius);overflow:hidden;}
.table-card th,.table-card td{padding:14px 10px;border-bottom:1px solid rgba(0,0,0,0.1);text-align:center;font-size:.95rem;}
body.light .table-card th{background:var(--primary);color:#fff;}
body.dark .table-card th{background:#374151;color:#f5f5f5;}
body.light .table-card td{background:#f9fafb;}
body.dark .table-card td{background:#1f2937;color:#f5f5f5;}
.table-card tr:hover td{background:#e0f2fe;}
.badge-normal,.badge-high,.badge-critical{padding:4px 10px;border-radius:var(--radius);font-weight:600;color:#fff;}
.badge-normal{background:var(--success);}
.badge-high{background:var(--high);}
.badge-critical{background:var(--critical);}
a{color:var(--primary);font-weight:600;text-decoration:none;}
a:hover{text-decoration:underline;}
@media(max-width:768px){
 .table-card table,.table-card thead,.table-card tbody,.table-card th,.table-card td,.table-card tr{display:block;}
 .table-card tr{margin-bottom:15px;}
 .table-card td{text-align:left;padding-left:50%;position:relative;}
 .table-card td::before{position:absolute;left:15px;top:12px;font-weight:500;white-space:nowrap;}
 .table-card td:nth-of-type(1)::before{content:"ID";}
 .table-card td:nth-of-type(2)::before{content:"Vehicle";}
 .table-card td:nth-of-type(3)::before{content:"Violation";}
 .table-card td:nth-of-type(4)::before{content:"Driver";}
 .table-card td:nth-of-type(5)::before{content:"Ticket Date";}
 .table-card td:nth-of-type(6)::before{content:"Issue Date";}
 .table-card td:nth-of-type(7)::before{content:"Emergency";}
 .table-card td:nth-of-type(8)::before{content:"Action";}
}
</style>
</head>
<body>
<div class="container">
  <h1>🚦 Ticket Management</h1>
  <?= $message ?>

  <div class="grid">
    <div class="card">
      <form method="POST">
        <h3>✅ Issue New Ticket</h3>
        <label>Vehicle:</label>
        <select name="vehicle_id" required>
          <option value="">-- Select Vehicle --</option>
          <?php $vehicles->data_seek(0);while($v=$vehicles->fetch_assoc()):?>
          <option value="<?=$v['vehicle_id']?>"><?=htmlspecialchars($v['vehicle_number'])?></option>
          <?php endwhile;?>
        </select>

        <label>Violation:</label>
        <select name="violation_id" required>
          <option value="">-- Select Violation --</option>
          <?php $violations->data_seek(0);while($vi=$violations->fetch_assoc()):?>
          <option value="<?=$vi['violation_id']?>"><?=htmlspecialchars($vi['violation_name'])?></option>
          <?php endwhile;?>
        </select>

        <label>Driver:</label>
        <select name="driver_id" required>
          <option value="">-- Select Driver --</option>
          <?php $drivers->data_seek(0);while($d=$drivers->fetch_assoc()):?>
          <option value="<?=$d['driver_id']?>"><?=htmlspecialchars($d['driver_name'])?></option>
          <?php endwhile;?>
        </select>

        <label>Ticket Date:</label>
        <input type="date" name="ticket_date" max="<?=date('Y-m-d')?>" required>
        <button type="submit" name="issue_ticket">Issue Ticket</button>
      </form>
    </div>

    <div class="card">
      <form method="POST">
        <h3>✏️ Update Tickets by Vehicle</h3>
        <label>Select Vehicle:</label>
        <select name="update_vehicle_id" required>
          <option value="">-- Select Vehicle --</option>
          <?php $vehicles->data_seek(0);while($v=$vehicles->fetch_assoc()):?>
          <option value="<?=$v['vehicle_id']?>"><?=htmlspecialchars($v['vehicle_number'])?></option>
          <?php endwhile;?>
        </select>

        <label>New Violation:</label>
        <select name="new_violation_id" required>
          <option value="">-- Select Violation --</option>
          <?php $violations->data_seek(0);while($vi=$violations->fetch_assoc()):?>
          <option value="<?=$vi['violation_id']?>"><?=htmlspecialchars($vi['violation_name'])?></option>
          <?php endwhile;?>
        </select>

        <label>New Driver:</label>
        <select name="new_driver_id" required>
          <option value="">-- Select Driver --</option>
          <?php $drivers->data_seek(0);while($d=$drivers->fetch_assoc()):?>
          <option value="<?=$d['driver_id']?>"><?=htmlspecialchars($d['driver_name'])?></option>
          <?php endwhile;?>
        </select>
        
        <button type="submit" name="update_by_vehicle">Update Tickets</button>
      </form>
    </div>
  </div>
  <div class="card table-card">
    <h3>📄 Existing Tickets</h3>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Vehicle</th><th>Violation</th><th>Driver</th>
          <th>Ticket Date</th><th>Issue Date</th><th>Emergency</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while($ticket=$tickets->fetch_assoc()):?>
        <tr>
          <td><?=$ticket['ticket_id']?></td>
          <td><?=htmlspecialchars($ticket['vehicle_number'])?></td>
          <td><?=htmlspecialchars($ticket['violation_name'])?></td>
          <td><?=htmlspecialchars($ticket['driver_name'])?></td>
          <td><?=$ticket['ticket_date']?></td>
          <td><?=$ticket['issue_date']?></td>
          <td><span class="badge-<?=strtolower($ticket['emergency_level'])?>"><?=$ticket['emergency_level']?></span></td>
          <td><a href="?delete_ticket_id=<?=$ticket['ticket_id']?>" onclick="return confirm('Delete this ticket?')">❌ Delete</a></td>
        </tr>
      <?php endwhile;?>
      </tbody>
    </table>
  </div>

  <div style="text-align:center;margin-top:10px;">
    <a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</div>
<script>
const currentTheme=localStorage.getItem('theme')||'light';
document.body.classList.add(currentTheme);
</script>
</body>
</html>
