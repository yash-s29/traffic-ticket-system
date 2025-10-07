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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --bg-light:#eef2f6;
  --bg-dark:#0f1724;
  --glass-light:rgba(255,255,255,0.75);
  --glass-dark:rgba(30,30,30,0.55);
  --text-light:#111827;
  --text-dark:#f5f5f5;
  --radius:16px;
  --transition:0.25s ease;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body.light{background:var(--bg-light);color:var(--text-light);}
body.dark{background:var(--bg-dark);color:var(--text-dark);}

.header-bar {
  width:100%;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:18px 28px;
  position:sticky;
  top:0;
  z-index:10;
  backdrop-filter:blur(10px);
  box-shadow:0 4px 12px rgba(0,0,0,0.2);
}
.header-bar h1 {
  font-size:1.5rem;
  font-weight:600;
}
.header-bar a {
  text-decoration:none;
  font-weight:500;
  color:#fff;
  background:rgba(255,255,255,0.15);
  padding:8px 16px;
  border-radius:var(--radius);
  transition:var(--transition);
}
.header-bar a:hover{background:rgba(255,255,255,0.25);}

.container {
  max-width:1200px;
  margin:40px auto;
  padding:0 20px;
}
.card {
  background:var(--glass-light);
  backdrop-filter:blur(18px) saturate(180%);
  border:1px solid rgba(255,255,255,0.3);
  border-radius:var(--radius);
  padding:30px;
  box-shadow:0 12px 28px rgba(0,0,0,0.12);
}
body.dark .card{
  background:var(--glass-dark);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 12px 28px rgba(0,0,0,0.35);
}
.card h2 {
  text-align:center;
  margin-bottom:20px;
  font-size:1.6rem;
  color:var(--primary);
  font-weight:600;
}

table {
  width:100%;
  border-collapse:separate;
  border-spacing:0 8px;
}
th,td {
  padding:14px 12px;
  text-align:center;
  font-size:0.95rem;
}
th {
  background:linear-gradient(to right,var(--primary),var(--primary-dark));
  color:#fff;
  font-weight:600;
}
tbody tr {
  background:rgba(255,255,255,0.15);
  border-radius:var(--radius);
  transition:var(--transition);
}
body.dark tbody tr{background:rgba(20,25,30,0.3);}
tbody tr:hover{background:rgba(37,99,235,0.08);transform:scale(1.01);}
td {
  border-bottom:1px solid rgba(0,0,0,0.05);
}

.badge {
  display:inline-block;
  padding:6px 14px;
  border-radius:999px;
  font-weight:500;
  background:rgba(0,0,0,0.06);
  color:inherit;
}
body.dark .badge{background:rgba(255,255,255,0.08);}

.print-link a {
  text-decoration:none;
  font-weight:600;
  padding:8px 16px;
  border-radius:999px;
  background:linear-gradient(to right,var(--primary),var(--primary-dark));
  color:#fff;
  display:inline-block;
  transition:all 0.2s;
}
.print-link a:hover{transform:scale(1.07);}

@media(max-width:768px){
  table,thead,tbody,th,td,tr{display:block;}
  thead tr{display:none;}
  tr{margin-bottom:15px;}
  td{padding-left:50%;position:relative;text-align:left;}
  td::before{position:absolute;left:15px;top:12px;font-weight:500;white-space:nowrap;}
  td:nth-of-type(1)::before{content:"ID";}
  td:nth-of-type(2)::before{content:"Vehicle";}
  td:nth-of-type(3)::before{content:"Driver";}
  td:nth-of-type(4)::before{content:"Violation";}
  td:nth-of-type(5)::before{content:"Ticket Date";}
  td:nth-of-type(6)::before{content:"Issue Date";}
  td:nth-of-type(7)::before{content:"Action";}
}
</style>
</head>
<body>
<div class="header-bar">
  <h1>📄 View Issued Tickets</h1>
  <a href="dashboard.php">⬅ Back to Dashboard</a>
</div>

<div class="container">
  <div class="card">
    <h2>All Tickets</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Vehicle</th>
          <th>Driver</th>
          <th>Violation</th>
          <th>Ticket Date</th>
          <th>Issue Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$tickets->fetch_assoc()): ?>
        <tr>
          <td><?= $row['ticket_id'] ?></td>
          <td><span class="badge"><?= htmlspecialchars($row['vehicle_number']) ?></span></td>
          <td><span class="badge"><?= htmlspecialchars($row['driver_name']) ?></span></td>
          <td><span class="badge"><?= htmlspecialchars($row['violation_name']) ?></span></td>
          <td><span class="badge"><?= $row['ticket_date'] ?></span></td>
          <td><span class="badge"><?= $row['issue_date'] ?></span></td>
          <td class="print-link">
            <a href="print_ticket.php?id=<?= $row['ticket_id'] ?>" target="_blank">🖨️ Print</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
const theme=localStorage.getItem('theme')||'light';
document.body.classList.add(theme);
</script>
</body>
</html>
