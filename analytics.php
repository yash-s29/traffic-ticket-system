<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$admin = htmlspecialchars($_SESSION["admin"] ?? '');

$total_tickets = 0;
$open_tickets = 0;
$closed_tickets = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM Tickets");
if ($row = $result->fetch_assoc()) $total_tickets = (int)$row['total'];

$result_open = $conn->query("SELECT COUNT(*) AS total FROM Tickets WHERE status='Open'");
if ($row = $result_open->fetch_assoc()) $open_tickets = (int)$row['total'];

$result_closed = $conn->query("SELECT COUNT(*) AS total FROM Tickets WHERE status='Closed'");
if ($row = $result_closed->fetch_assoc()) $closed_tickets = (int)$row['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📊 Analytics Dashboard – Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
.header-bar h1 {font-size:1.5rem; font-weight:600;}
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
  display:flex;
  flex-direction:column;
  gap:30px;
}
.cards {
  display:flex;
  gap:20px;
  flex-wrap:wrap;
  justify-content:center;
}
.card {
  background:var(--glass-light);
  backdrop-filter:blur(18px) saturate(180%);
  border:1px solid rgba(255,255,255,0.3);
  border-radius:var(--radius);
  padding:25px 20px;
  width:220px;
  text-align:center;
  box-shadow:0 12px 28px rgba(0,0,0,0.12);
  transition:var(--transition);
}
body.dark .card{
  background:var(--glass-dark);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 12px 28px rgba(0,0,0,0.35);
}
.card .num {font-size:36px; font-weight:700; margin-top:8px;}
.card .label {color:#6b7280; font-weight:600; font-size:14px; margin-top:4px;}
body.dark .card .label{color:#9aa6b2;}
.card:hover{transform:scale(1.03);}

.chart-card {
  background:var(--glass-light);
  backdrop-filter:blur(18px) saturate(180%);
  border-radius:var(--radius);
  padding:25px;
  min-height:320px;
  box-shadow:0 12px 28px rgba(0,0,0,0.12);
  transition:var(--transition);
}
body.dark .chart-card{
  background:var(--glass-dark);
  box-shadow:0 12px 28px rgba(0,0,0,0.35);
}

footer {color:#6b7280; font-size:13px; text-align:center; margin:24px 0;}
body.dark footer{color:#9aa6b2;}

@media(max-width:768px){
  .cards{flex-direction:column; align-items:center;}
}
</style>
</head>
<body>

<div class="header-bar">
  <h1>📊 Analytics Dashboard</h1>
  <a href="dashboard.php">⬅ Back to Dashboard</a>
</div>

<div class="container">
  <div class="cards">
    <div class="card">
      <div class="label">Total Tickets</div>
      <div class="num"><?= $total_tickets ?></div>
    </div>
    <div class="card">
      <div class="label">Open Tickets</div>
      <div class="num"><?= $open_tickets ?></div>
    </div>
    <div class="card">
      <div class="label">Closed Tickets</div>
      <div class="num"><?= $closed_tickets ?></div>
    </div>
  </div>

  <div class="chart-card">
    <canvas id="ticketsChart"></canvas>
  </div>
</div>

<footer>© <?= date('Y') ?> Traffic Admin • Analytics overview of tickets.</footer>

<script>
const theme = localStorage.getItem('theme') || 'light';
if(theme==='dark') document.body.classList.add('dark');

const ctx = document.getElementById('ticketsChart').getContext('2d');
new Chart(ctx,{
    type:'bar',
    data:{
        labels:['Total Tickets','Open Tickets','Closed Tickets'],
        datasets:[{
            label:'Tickets',
            data:[<?= $total_tickets ?>, <?= $open_tickets ?>, <?= $closed_tickets ?>],
            backgroundColor:'#2563eb',
            borderRadius:8,
            barThickness:40
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{display:false},
            tooltip:{enabled:true}
        },
        scales:{
            x:{
                ticks:{color:theme==='dark'?'#f5f5f5':'#111827', font:{weight:600}},
                grid:{display:false}
            },
            y:{
                beginAtZero:true,
                ticks:{color:theme==='dark'?'#f5f5f5':'#111827'},
                grid:{color:theme==='dark'?'rgba(255,255,255,0.1)':'rgba(0,0,0,0.05)'}
            }
        }
    }
});
</script>

</body>
</html>
