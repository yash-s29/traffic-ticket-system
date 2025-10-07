<?php
session_start();
require 'db.php';

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$admin = htmlspecialchars($_SESSION["admin"] ?? '');
$message = '';
$ticket = null;

if (isset($_GET['q']) && $_GET['q'] !== '') {
    $q = $conn->real_escape_string($_GET['q']);
    $result = $conn->query("
        SELECT t.ticket_id, t.ticket_code, t.ticket_date, t.status, 
               v.vehicle_number, d.driver_name, vio.violation_name 
        FROM Tickets t
        JOIN Vehicles v ON t.vehicle_id = v.vehicle_id
        JOIN Drivers d ON t.driver_id = d.driver_id
        JOIN Violations vio ON t.violation_id = vio.violation_id
        WHERE t.ticket_id='$q' OR t.ticket_code='$q'
    ");
    if ($result && $result->num_rows > 0) {
        $ticket = $result->fetch_assoc();
        if (!$ticket['ticket_code']) $ticket['ticket_code'] = 'TICKET-'.$ticket['ticket_id'];
    } else {
        $message = "Ticket not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📱 Verify Ticket — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary:#2563eb;
  --secondary:#06b6d4;
  --bg-light:#eef2f6;
  --bg-dark:#0f1724;
  --glass-light:rgba(255,255,255,0.85);
  --glass-dark:rgba(20,25,30,0.6);
  --text-light:#111827;
  --text-dark:#f5f5f5;
  --radius:20px;
  --transition:0.3s ease;
}

* {margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; transition:var(--transition);}
body.light {background:var(--bg-light); color:var(--text-light);}
body.dark {background:var(--bg-dark); color:var(--text-dark);}

header {padding:30px 20px; text-align:center;}
header h1 {font-size:2.2rem; font-weight:700; margin-bottom:8px;}
header .subtitle {color:#6b7280; font-size:14px;}
body.dark header .subtitle {color:#9aa6b2;}

.container {width:90%; max-width:850px; margin:0 auto; display:flex; flex-direction:column; gap:32px; padding-bottom:50px;}

.card {
  background:var(--glass-light);
  border-radius:var(--radius);
  padding:30px 28px;
  box-shadow:0 16px 36px rgba(0,0,0,0.12);
  backdrop-filter:blur(18px) saturate(180%);
  border:1px solid rgba(255,255,255,0.2);
  display:flex;
  flex-direction:column;
  gap:20px;
  transition:var(--transition);
}
body.dark .card {
  background:var(--glass-dark);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 16px 36px rgba(0,0,0,0.3);
}

form label,input {
  display:block;
  width:100%;
  margin-bottom:14px;
  padding:14px 16px;
  border-radius:14px;
  border:1px solid #ccc;
  font-size:14px;
  background:white;
}
body.dark form label,input {background:rgba(255,255,255,0.05);border:1px solid #555;color:#e6eef8;}
form button {
  padding:14px 18px;
  border:none;
  border-radius:16px;
  font-weight:700;
  font-size:15px;
  background:linear-gradient(90deg,var(--primary),var(--secondary));
  color:white;
  cursor:pointer;
  box-shadow:0 6px 12px rgba(0,0,0,0.15);
  transition:all 0.25s ease;
}
form button:hover {transform:translateY(-2px); box-shadow:0 8px 16px rgba(0,0,0,0.2);}
.ticket-info {margin-top:20px; display:flex; flex-direction:column; gap:12px;}
.ticket-info p {margin:0; font-size:15px; line-height:1.5;}
.ticket-info .status {
  font-weight:700;
  padding:4px 12px;
  border-radius:12px;
  display:inline-block;
  color:#fff;
  background:linear-gradient(90deg, #2563eb, #06b6d4);
}
.ticket-info .status.closed {background:linear-gradient(90deg,#10b981,#059669);}
.qr-code {margin-top:16px; display:flex; justify-content:center;}
.message {color:#ef4444; font-weight:700; margin-top:12px; text-align:center; font-size:14px;}

footer {color:#6b7280; font-size:13px; text-align:center; margin:30px 0;}
body.dark footer{color:#9aa6b2;}

@media(max-width:600px){
  header h1{font-size:1.6rem;}
  form button{width:100%;}
}
</style>
</head>
<body>

<header>
  <h1>📱 Verify Ticket</h1>
  <div class="subtitle">Search by Ticket ID or Ticket Code to quickly check ticket details.</div>
</header>

<div class="container">
  <div class="card">
    <form method="get">
      <label for="q">Enter Ticket ID or Code</label>
      <input type="text" name="q" id="q" placeholder="Ticket ID or Code" required value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
      <button type="submit">Verify</button>
    </form>

    <?php if($message): ?>
      <p class="message"><?= $message ?></p>
    <?php elseif($ticket): ?>
      <div class="ticket-info">
        <p><strong>Ticket ID:</strong> <?= $ticket['ticket_id'] ?></p>
        <p><strong>Ticket Code:</strong> <?= $ticket['ticket_code'] ?></p>
        <p><strong>Vehicle:</strong> <?= $ticket['vehicle_number'] ?></p>
        <p><strong>Driver:</strong> <?= $ticket['driver_name'] ?></p>
        <p><strong>Violation:</strong> <?= $ticket['violation_name'] ?></p>
        <p><strong>Date:</strong> <?= $ticket['ticket_date'] ?></p>
        <p><strong>Status:</strong> 
          <span class="status <?= strtolower($ticket['status']) === 'closed' ? 'closed':'' ?>"><?= $ticket['status'] ?></span>
        </p>

        <div class="qr-code" id="qrcode"></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer>© <?= date('Y') ?> Traffic Admin • Verify tickets efficiently.</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php if($ticket): ?>
<script>
const qrColor = "<?= strtolower($ticket['status'])==='closed' ? '#10b981' : '#2563eb' ?>";
new QRCode(document.getElementById("qrcode"), {
    text: "<?= $ticket['ticket_code'] ?>",
    width: 180,
    height: 180,
    colorDark : qrColor,
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
});
</script>
<?php endif; ?>

<script>
(function(){
    const saved = localStorage.getItem('theme') || 'light';
    if(saved==='dark') document.body.classList.add('dark');
})();
</script>
</body>
</html>
