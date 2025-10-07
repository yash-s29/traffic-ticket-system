<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$admin = htmlspecialchars($_SESSION["admin"] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🚨 Emergency Alerts — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --secondary:#06b6d4;
  --bg-light:#eef2f6;
  --bg-dark:#0f1724;
  --glass-light:rgba(255,255,255,0.85);
  --glass-dark:rgba(20,25,30,0.6);
  --text-light:#111827;
  --text-dark:#f5f5f5;
  --radius:16px;
  --transition:0.25s ease;
  --severity-critical:#2563eb;
  --severity-high:#1e40af;
}

* {
  margin:0; padding:0; box-sizing:border-box;
  font-family:'Inter',sans-serif;
  transition:var(--transition);
}
body.light {
  background:var(--bg-light);
  color:var(--text-light);
}
body.dark {
  background:var(--bg-dark);
  color:var(--text-dark);
}

header {
  padding:28px 20px;
  text-align:center;
}
header h1 {
  font-size:2rem;
  font-weight:700;
  margin-bottom:6px;
  color:var(--primary);
}
header .subtitle {
  color:#6b7280;
  font-size:14px;
}
body.dark header .subtitle { color:#9aa6b2; }

.container {
  width:90%;
  max-width:1200px;
  margin:0 auto;
  display:flex;
  flex-direction:column;
  gap:28px;
}
.card {
  background:var(--glass-light);
  border-radius:var(--radius);
  padding:24px;
  box-shadow:0 16px 36px rgba(0,0,0,0.12);
  backdrop-filter:blur(18px) saturate(180%);
  border:1px solid rgba(255,255,255,0.2);
}
body.dark .card {
  background:var(--glass-dark);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 16px 36px rgba(0,0,0,0.35);
}

table {
  width:100%;
  border-collapse:separate;
  border-spacing:0 8px;
}
th, td {
  padding:14px;
  text-align:left;
  transition:var(--transition);
}
th {
  color:var(--primary);
  font-weight:700;
}
body.dark th { color:var(--secondary); }
tr {
  background:var(--glass-light);
  border-radius:var(--radius);
}
body.dark tr { background:var(--glass-dark); }
tr:hover {
  transform:scale(1.01);
  cursor:pointer;
}

.badge-severity {
  display:inline-block;
  padding:4px 10px;
  border-radius:12px;
  font-weight:600;
  font-size:0.85rem;
  color:#fff;
}
.badge-critical {
  background:linear-gradient(90deg,var(--severity-critical),var(--primary-dark));
}
.badge-high {
  background:linear-gradient(90deg,var(--severity-high),var(--secondary));
}

@keyframes sirenMove {
  0%,100% { transform:rotate(0deg) translateY(0); opacity:1; }
  25% { transform:rotate(-12deg) translateY(-2px); opacity:0.8; }
  50% { transform:rotate(0deg) translateY(0); opacity:1; }
  75% { transform:rotate(12deg) translateY(2px); opacity:0.8; }
}
.siren {
  display:inline-block;
  margin-right:6px;
  animation:sirenMove 1s infinite ease-in-out;
  filter:drop-shadow(0 0 6px rgba(37,99,235,0.8));
}

.details {
  display:none;
  background:rgba(37,99,235,0.05);
  font-size:14px;
  color:#374151;
  padding:12px 16px;
  border-radius:12px;
  margin-top:4px;
  line-height:1.5;
}
body.dark .details {
  background:rgba(37,99,235,0.2);
  color:#d1d5db;
}

footer {
  color:#6b7280;
  font-size:13px;
  text-align:center;
  margin:30px 0;
}
body.dark footer { color:#9aa6b2; }

@media(max-width:768px) {
  th, td { font-size:13px; padding:10px; }
  header h1 { font-size:1.5rem; }
}
</style>
</head>
<body>

<header>
  <h1>🚨 Emergency Alerts</h1>
  <div class="subtitle">Prioritized tickets — Critical & High</div>
</header>

<div class="container">
  <div class="card">
    <table id="alertsTable">
      <thead>
        <tr>
          <th>Ticket ID</th>
          <th>Vehicle</th>
          <th>Driver</th>
          <th>Violation</th>
          <th>Date</th>
          <th>Severity</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="6" style="text-align:center;">Loading alerts...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<footer>© <?= date('Y') ?> Traffic Admin • Emergency ticket monitoring</footer>

<audio id="criticalBeep" src="beep.mp3" preload="auto"></audio>

<script>
(function(){
  const saved = localStorage.getItem('theme') || 'light';
  if(saved === 'dark') document.body.classList.add('dark');
})();

function toggleDetails(id){
  const row = document.getElementById(id);
  row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
}

let lastCriticalIds = [];
function fetchAlerts(){
  fetch('fetch_emergency_alerts.php')
  .then(res => res.json())
  .then(data => {
    const tbody = document.querySelector('#alertsTable tbody');
    tbody.innerHTML = '';
    let criticalThisLoad = [];

    if(!data || data.length === 0){
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No emergency alerts found.</td></tr>';
      return;
    }

    data.forEach(a => {
      const severityClass = a.emergency_level === 'Critical' ? 'badge-critical' : 'badge-high';
      if(a.emergency_level === 'Critical') criticalThisLoad.push(a.ticket_id);

      const row = document.createElement('tr');
      row.onclick = () => toggleDetails('details-'+a.ticket_id);
      row.innerHTML = `
        <td>${a.emergency_level === 'Critical' ? '<span class="siren">🚨</span>' : ''}${a.ticket_id}</td>
        <td>${a.vehicle_number || 'N/A'}</td>
        <td>${a.driver_name || 'N/A'}</td>
        <td>${a.violation_name || 'N/A'}</td>
        <td>${a.ticket_date || 'N/A'}</td>
        <td><span class="${severityClass}">${a.emergency_level}</span></td>
      `;
      tbody.appendChild(row);

      const detailRow = document.createElement('tr');
      detailRow.id = 'details-'+a.ticket_id;
      detailRow.className = 'details';
      detailRow.innerHTML = `
        <td colspan="6">
          <strong>Driver Contact:</strong> ${a.driver_contact || 'N/A'}<br>
          <strong>Vehicle Type:</strong> ${a.vehicle_type || 'N/A'}<br>
          <strong>Violation Details:</strong> ${a.violation_description || 'N/A'}<br>
          <strong>Fine:</strong> $${a.fine_amount || 0}<br>
          <strong>Remarks:</strong> ${a.remarks || 'N/A'}
        </td>
      `;
      tbody.appendChild(detailRow);
    });
    const newCritical = criticalThisLoad.filter(id => !lastCriticalIds.includes(id));
    if(newCritical.length > 0){
      const audio = document.getElementById('criticalBeep');
      audio.play().catch(() => console.log('Autoplay blocked by browser.'));
    }
    lastCriticalIds = criticalThisLoad;
  })
  .catch(err => console.error('Error fetching alerts:', err));
}

fetchAlerts();
setInterval(fetchAlerts, 30000);
</script>
</body>
</html>
