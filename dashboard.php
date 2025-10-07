<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$admin = htmlspecialchars($_SESSION["admin"] ?? '');
$ticket_count = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM Tickets");
if ($row = $result->fetch_assoc()) $ticket_count = (int)$row['total'];
$status_exists = false;
$columns = $conn->query("SHOW COLUMNS FROM Tickets");
while ($col = $columns->fetch_assoc()) {
    if ($col['Field'] === 'status') { $status_exists = true; break; }
}

$open_tickets = 0;
$closed_tickets = 0;
if ($status_exists) {
    $result_open = $conn->query("SELECT COUNT(*) as total FROM Tickets WHERE status='Open'");
    if ($row = $result_open->fetch_assoc()) $open_tickets = (int)$row['total'];

    $result_closed = $conn->query("SELECT COUNT(*) as total FROM Tickets WHERE status='Closed'");
    if ($row = $result_closed->fetch_assoc()) $closed_tickets = (int)$row['total'];
}

$current = basename($_SERVER['PHP_SELF']);
function isActive($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Traffic Tickets</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-light: #3b82f6;
    --primary-dark: #2563eb;
    --bg-light: #f0f4ff;
    --bg-dark: #0f1724;
    --card-light: rgba(255,255,255,0.85);
    --card-dark: rgba(20,25,30,0.6);
    --text-light: #11243a;
    --text-dark: #e6eef8;
    --muted-light: #6b7280;
    --muted-dark: #9aa6b2;
    --success: #10b981;
    --danger: #ef4444;
    --radius: 20px;
    --shadow: 0 15px 40px rgba(0,0,0,0.08);
}
body { font-family: 'Inter', sans-serif; margin:0; min-height:100vh; transition: all 0.3s ease; -webkit-font-smoothing: antialiased; }
body.light { background: linear-gradient(135deg, var(--bg-light), #ffffff); color: var(--text-light); }
body.dark { background: linear-gradient(135deg, var(--bg-dark), #071024); color: var(--text-dark); }

.sidebar {
    position: fixed; left: 20px; top: 20px; bottom: 20px; width: 280px; padding: 30px 20px;
    background: rgba(255,255,255,0.15); backdrop-filter: blur(12px) saturate(180%);
    border-radius: var(--radius); box-shadow: var(--shadow);
    display: flex; flex-direction: column; justify-content: space-between; gap:12px;
}
body.dark .sidebar { background: rgba(20,25,30,0.6); }

.brand { text-align:center; margin-bottom:20px; }
.brand .emoji { font-size:32px; margin-right:6px; }
.brand .title { font-weight:700; font-size:22px; background: linear-gradient(90deg,var(--primary-light),var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

nav[role="navigation"] { display:flex; flex-direction:column; gap:10px; margin-top:10px; }
.nav-link { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:14px; text-decoration:none; font-weight:600; color:inherit; transition: all 0.2s ease; }
.nav-link .ico { width:24px; text-align:center; font-size:18px; }
.nav-link:hover, .nav-link.active { transform: translateX(6px); background: linear-gradient(90deg, rgba(59,130,246,0.1), rgba(6,182,212,0.08)); box-shadow: 0 8px 22px rgba(10,25,60,0.06); }

.meta { display:flex; flex-direction:column; gap:12px; align-items:center; }
.btn { cursor:pointer; padding:10px 14px; border-radius:14px; border:none; font-weight:700; background: linear-gradient(90deg,var(--primary-light),var(--primary-dark)); color:white; box-shadow: var(--shadow); transition: transform 0.2s ease; }
.btn:hover { transform: translateY(-2px); }
.logout { font-weight:700; text-decoration:none; padding:10px 14px; border-radius:14px; background:transparent; color:inherit; transition: transform 0.2s ease; }
.logout:hover { background: rgba(255,255,255,0.08); transform: translateY(-2px); }

.main { margin-left:320px; padding:40px; min-height:100vh; display:flex; flex-direction:column; gap:30px; }
header.topbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
.top-left h1 { font-size:28px; font-weight:700; }
.top-left .subtitle { font-size:14px; color: var(--muted-light); }
body.dark .top-left .subtitle { color: var(--muted-dark); }

.grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:24px; }
.card { background: var(--card-light); border-radius: var(--radius); padding:24px; box-shadow: var(--shadow); transition: transform 0.2s ease; }
body.dark .card { background: var(--card-dark); }
.card:hover { transform: translateY(-4px); }

.stat { display:flex; flex-direction:column; gap:6px; }
.stat .num { font-size:36px; font-weight:800; }
.stat .label { color: var(--muted-light); font-weight:600; font-size:14px; }
body.dark .stat .label { color: var(--muted-dark); }

.quick-actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:14px; }
.quick-actions a { display:inline-flex; align-items:center; gap:6px; text-decoration:none; font-weight:600; padding:10px 16px; border-radius:14px; background: linear-gradient(90deg,#f5f8ff,#fff); color:inherit; box-shadow:0 6px 20px rgba(0,0,0,0.05); transition: transform 0.2s ease; }
.quick-actions a:hover { transform: translateY(-2px); }
body.dark .quick-actions a { background:transparent; border:1px solid rgba(255,255,255,0.08); }

.search { display:flex; align-items:center; gap:8px; padding:8px 14px; border-radius:14px; background: var(--card-light); box-shadow: var(--shadow); }
body.dark .search { background: var(--card-dark); }
.search input { border:none; outline:none; background:transparent; font-size:14px; color:inherit; width:220px; }

footer { font-size:13px; color: var(--muted-light); margin-top:20px; }
body.dark footer { color: var(--muted-dark); }

@media(max-width:980px){.sidebar{left:12px;width:240px;}.main{margin-left:280px;padding:28px;}.search input{width:140px;}}
@media(max-width:720px){.sidebar{display:none;}.main{margin-left:16px;padding:20px;} header.topbar{flex-direction:column;align-items:flex-start;}}
</style>
</head>
<body class="light">

<aside class="sidebar">
    <div>
        <div class="brand"><span class="emoji">🚔</span><span class="title">Admin Panel</span></div>
        <nav role="navigation">
            <a class="nav-link <?= isActive('dashboard.php', $current) ?>" href="dashboard.php"><span class="ico">🏠</span> Dashboard</a>
            <a class="nav-link <?= isActive('issue_ticket.php', $current) ?>" href="issue_ticket.php"><span class="ico">🎫</span> Issue Ticket</a>
            <a class="nav-link <?= isActive('view_ticket.php', $current) ?>" href="view_ticket.php"><span class="ico">📄</span> View Tickets</a>
            <a class="nav-link <?= isActive('analytics.php', $current) ?>" href="analytics.php"><span class="ico">📊</span> Analytics</a>
            <a class="nav-link <?= isActive('verify_ticket.php', $current) ?>" href="verify_ticket.php"><span class="ico">📱</span> Verify Ticket (QR)</a>
            <a class="nav-link <?= isActive('emergency_alerts.php', $current) ?>" href="emergency_alerts.php"><span class="ico">🚨</span> Emergency Alerts</a>
        </nav>
    </div>
    <div class="meta">
        <button id="themeToggle" class="btn" aria-pressed="false">🌓 Theme</button>
        <a class="logout" href="logout.php">🚪 Logout</a>
    </div>
</aside>

<main class="main">
    <header class="topbar">
        <div class="top-left">
            <h1>👋 Welcome, Admin</h1>
            <div class="subtitle">Manage tickets, verify records, and monitor analytics — all in one place.</div>
        </div>
        <div class="top-actions">
            <div class="search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M21 21l-4.35-4.35" stroke="#2563eb" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="11" cy="11" r="6" stroke="#2563eb" stroke-width="1.6"/>
                </svg>
                <input id="searchInput" placeholder="Search ticket ID, vehicle...">
            </div>
        </div>
    </header>

    <section class="grid">
        <div class="card"><div class="stat"><div class="label">Total Tickets</div><div class="num"><?= $ticket_count ?></div></div></div>
        <?php if($status_exists): ?>
        <div class="card"><div class="stat"><div class="label">Open Tickets</div><div class="num"><?= $open_tickets ?></div></div></div>
        <div class="card"><div class="stat"><div class="label">Closed Tickets</div><div class="num"><?= $closed_tickets ?></div></div></div>
        <?php endif; ?>
    </section>
    
    <section class="card">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div style="font-weight:700">Quick Actions</div>
            <div style="font-size:13px; color: var(--muted-light)">Jump between pages</div>
        </div>
        <div class="quick-actions">
            <a href="issue_ticket.php">🎫 Issue Ticket</a>
            <a href="view_ticket.php">📄 View Tickets</a>
            <a href="analytics.php">📊 Analytics</a>
            <a href="verify_ticket.php">📱 Verify (QR)</a>
            <a href="emergency_alerts.php">🚨 Emergency Alerts</a>
        </div>
    </section>

    <footer>
        © <?= date('Y') ?> Traffic Admin • QR on printed tickets, Verify Ticket page, Emergency alerts & Analytics.
    </footer>
</main>

<script>
(function(){
    const saved = localStorage.getItem('theme') || 'light';
    if(saved==='dark') document.body.classList.add('dark');

    const btn = document.getElementById('themeToggle');
    function setAria(){ if(!btn) return; btn.setAttribute('aria-pressed', document.body.classList.contains('dark')?'true':'false'); }
    setAria();

    if(btn){
        btn.addEventListener('click', function(){
            document.body.classList.toggle('dark');
            localStorage.setItem('theme', document.body.classList.contains('dark')?'dark':'light');
            setAria();
        });
        btn.addEventListener('keydown', function(e){
            if(e.key===' '||e.key==='Enter'){ e.preventDefault(); btn.click(); }
        });
    }

    const searchInput = document.getElementById('searchInput');
    if(searchInput){
        searchInput.addEventListener('keydown', function(e){
            if(e.key==='Enter'){
                const q = searchInput.value.trim();
                if(!q) return;
                location.href='view_ticket.php?q='+encodeURIComponent(q);
            }
        });
    }
})();
</script>
</body>
</html>
