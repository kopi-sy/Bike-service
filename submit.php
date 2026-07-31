<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli("localhost", "root", "", "bike_service", 3306);

$pageTitle = "MotoServe — Booking";
$success   = false;
$message   = "";

if ($conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error);
} else {
    $name    = trim($_POST['name']    ?? '');
    $bike    = trim($_POST['bike']    ?? '');
    $service = trim($_POST['service'] ?? '');
    $mobile  = trim($_POST['mobile']  ?? '');

    if ($name === '' || $bike === '' || $service === '' || $mobile === '') {
        $message = "All fields are required. Please go back and fill in the form.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $message = "Mobile number must be exactly 10 digits.";
    } else {
        $createTableSql = "
        CREATE TABLE IF NOT EXISTS booking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            bike VARCHAR(100) NOT NULL,
            service VARCHAR(100) NOT NULL,
            mobile VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if (!$conn->query($createTableSql)) {
            $message = "Table creation failed: " . htmlspecialchars($conn->error);
        } else {
            $stmt = $conn->prepare("INSERT INTO booking (name, bike, service, mobile) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $message = "Prepare failed: " . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param("ssss", $name, $bike, $service, $mobile);
                if ($stmt->execute()) {
                    $success = true;
                    $message = "Your bike service has been booked successfully!";
                } else {
                    $message = "Booking failed: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --accent: #f97316;
      --bg:     #0a0a0f;
      --border: rgba(255,255,255,0.08);
      --text:   #f1f1f1;
      --muted:  #9ca3af;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      overflow: hidden;
    }
    body::before {
      content: '';
      position: fixed; inset: 0;
      background:
        radial-gradient(ellipse 70% 60% at 20% -10%, rgba(249,115,22,0.2) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 110%, rgba(251,146,60,0.14) 0%, transparent 55%);
      pointer-events: none;
    }
    header {
      position: fixed; top: 0; left: 0; right: 0;
      padding: 20px 40px;
      display: flex; align-items: center; gap: 12px;
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(12px);
      background: rgba(10,10,15,0.7);
      z-index: 10;
    }
    .logo-icon {
      width: 38px; height: 38px;
      background: linear-gradient(135deg, #f97316, #fb923c);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 20px; box-shadow: 0 0 18px rgba(249,115,22,0.4);
    }
    .brand { font-size: 1.3rem; font-weight: 800; }
    .brand span { color: var(--accent); }

    .result-card {
      position: relative; z-index: 10;
      width: min(460px, calc(100% - 48px));
      background: rgba(255,255,255,0.045);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 48px 40px;
      backdrop-filter: blur(20px);
      box-shadow: 0 24px 64px rgba(0,0,0,0.6);
      text-align: center;
      animation: pop 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes pop {
      from { opacity: 0; transform: scale(0.9) translateY(20px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .icon-circle {
      width: 80px; height: 80px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 38px;
      margin: 0 auto 24px;
    }
    .icon-circle.ok  { background: rgba(34,197,94,0.15);  box-shadow: 0 0 32px rgba(34,197,94,0.25); }
    .icon-circle.err { background: rgba(239,68,68,0.15);  box-shadow: 0 0 32px rgba(239,68,68,0.25); }

    h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px; }
    p  { color: var(--muted); line-height: 1.7; font-size: 0.95rem; margin-bottom: 32px; }

    <?php if ($success): ?>
    .detail-box {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      text-align: left;
      margin-bottom: 28px;
      font-size: 0.88rem;
    }
    .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); }
    .detail-row:last-child { border-bottom: none; }
    .detail-key { color: var(--muted); }
    .detail-val { font-weight: 600; color: var(--text); }
    <?php endif; ?>

    .btn-row { display: flex; gap: 12px; justify-content: center; }
    .btn {
      padding: 13px 22px; border-radius: 10px; font-family: 'Inter', sans-serif;
      font-size: 0.9rem; font-weight: 700; cursor: pointer; text-decoration: none;
      transition: transform 0.2s, box-shadow 0.2s;
      display: inline-flex; align-items: center; gap: 6px;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn-primary {
      background: linear-gradient(135deg, #f97316, #fb923c);
      color: #fff; border: none;
      box-shadow: 0 6px 20px rgba(249,115,22,0.35);
    }
    .btn-primary:hover { box-shadow: 0 10px 28px rgba(249,115,22,0.5); }
    .btn-ghost {
      background: transparent; color: var(--muted);
      border: 1px solid var(--border);
    }
    .btn-ghost:hover { color: var(--text); border-color: rgba(249,115,22,0.4); }
  </style>
</head>
<body>
  <header>
    <div class="logo-icon">🏍️</div>
    <div class="brand">Moto<span>Serve</span></div>
  </header>

  <div class="result-card">
    <?php if ($success): ?>
      <div class="icon-circle ok">✅</div>
      <h2>Booking Confirmed!</h2>
      <p><?= htmlspecialchars($message) ?><br />We'll contact you at <strong><?= htmlspecialchars($mobile) ?></strong> to confirm your slot.</p>
      <div class="detail-box">
        <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val"><?= htmlspecialchars($name) ?></span></div>
        <div class="detail-row"><span class="detail-key">Bike</span><span class="detail-val"><?= htmlspecialchars($bike) ?></span></div>
        <div class="detail-row"><span class="detail-key">Service</span><span class="detail-val"><?= htmlspecialchars($service) ?></span></div>
        <div class="detail-row"><span class="detail-key">Mobile</span><span class="detail-val"><?= htmlspecialchars($mobile) ?></span></div>
      </div>
      <div class="btn-row">
        <a href="index.html" class="btn btn-ghost">← New Booking</a>
        <a href="view.php" class="btn btn-primary">📋 View All Bookings</a>
      </div>
    <?php else: ?>
      <div class="icon-circle err">❌</div>
      <h2>Something Went Wrong</h2>
      <p><?= htmlspecialchars($message) ?></p>
      <div class="btn-row">
        <a href="index.html" class="btn btn-primary">← Try Again</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>