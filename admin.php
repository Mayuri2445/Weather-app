<?php
session_start();

// --- Admin credentials ---
$admin_email = "mayuriboharpi@gmail.com";
$admin_password = "mayu123";

// --- Log file ---
$logFile = "weather_log.json";

// ---------- Auth actions ----------
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: admin.php");
  exit;
}

if (isset($_GET['clear'])) {
  file_put_contents($logFile, json_encode([], JSON_PRETTY_PRINT));
  header("Location: admin.php");
  exit;
}

$error = "";

// ---------- Login ----------
if (isset($_POST['login'])) {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  if ($email === $admin_email && $password === $admin_password) {
    $_SESSION['admin_logged_in'] = true;
  } else {
    $error = "❌ Invalid email or password.";
  }
}

// ---------- Helpers ----------
function read_logs($file) {
  if (!file_exists($file)) return [];
  $data = json_decode(file_get_contents($file), true);
  return is_array($data) ? $data : [];
}

// condition → emoji/icon
function condition_icon($desc) {
  $d = strtolower($desc);
  if (strpos($d, 'thunder') !== false) return "⛈";
  if (strpos($d, 'storm') !== false)   return "⛈";
  if (strpos($d, 'drizzle') !== false) return "🌦";
  if (strpos($d, 'rain') !== false)    return "🌧";
  if (strpos($d, 'snow') !== false)    return "❄";
  if (strpos($d, 'mist') !== false || strpos($d, 'haze') !== false || strpos($d, 'fog') !== false) return "🌫";
  if (strpos($d, 'cloud') !== false)   return "☁";
  if (strpos($d, 'clear') !== false)   return "☀";
  return "🌤";
}

// condition → color class
function condition_class($desc) {
  $d = strtolower($desc);
  if (strpos($d, 'thunder') !== false || strpos($d, 'storm') !== false) return "cond-storm";
  if (strpos($d, 'rain') !== false || strpos($d, 'drizzle') !== false)  return "cond-rain";
  if (strpos($d, 'snow') !== false)                                     return "cond-snow";
  if (strpos($d, 'cloud') !== false)                                    return "cond-cloud";
  if (strpos($d, 'clear') !== false)                                    return "cond-clear";
  if (strpos($d, 'mist') !== false || strpos($d, 'haze') !== false || strpos($d, 'fog') !== false) return "cond-haze";
  return "cond-other";
}

// temp → row tone
function temp_class($t) {
  if ($t === '' || $t === null) return '';
  if ($t >= 35) return "row-hot";
  if ($t <= 10) return "row-cold";
  return "row-mild";
}

// ---------- Actions: delete ----------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['idx']) && isset($_SESSION['admin_logged_in'])) {
  $idx = (int)$_GET['idx'];
  $logs = read_logs($logFile);
  if (isset($logs[$idx])) {
    array_splice($logs, $idx, 1);
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
  }
  // Keep query params for sorting/paging
  $qs = $_GET;
  unset($qs['action'], $qs['idx']);
  header("Location: admin.php?".http_build_query($qs));
  exit;
}

// ---------- Actions: export CSV ----------
if (isset($_GET['export']) && $_GET['export'] === 'csv' && isset($_SESSION['admin_logged_in'])) {
  $logs = read_logs($logFile);

  // Optional: apply sorting (same as UI params)
  $sort = $_GET['sort'] ?? 'time';
  $dir  = strtolower($_GET['dir'] ?? 'desc');

  $keyMap = ['city' => 'city','temp' => 'temp','desc' => 'desc','humidity' => 'humidity','wind' => 'wind','time' => 'time'];
  $k = $keyMap[$sort] ?? 'time';

  usort($logs, function($a, $b) use ($k, $dir) {
    $va = $a[$k] ?? '';
    $vb = $b[$k] ?? '';
    if (is_numeric($va) && is_numeric($vb)) {
      $cmp = $va <=> $vb;
    } else {
      $cmp = strcasecmp((string)$va, (string)$vb);
    }
    return ($dir === 'desc') ? -$cmp : $cmp;
  });

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=weather_report.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['City','Temperature (°C)','Condition','Humidity (%)','Wind (m/s)','Time (IST)']);
  foreach ($logs as $r) {
    fputcsv($out, [
      $r['city'] ?? '',
      $r['temp'] ?? '',
      $r['desc'] ?? '',
      $r['humidity'] ?? '',
      $r['wind'] ?? '',
      $r['time'] ?? ''
    ]);
  }
  fclose($out);
  exit;
}

// ---------- Actions: export PDF (print-friendly HTML) ----------
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && isset($_SESSION['admin_logged_in'])) {
  $logs = read_logs($logFile);

  // Sorting like UI
  $sort = $_GET['sort'] ?? 'time';
  $dir  = strtolower($_GET['dir'] ?? 'desc');
  $keyMap = ['city' => 'city','temp' => 'temp','desc' => 'desc','humidity' => 'humidity','wind' => 'wind','time' => 'time'];
  $k = $keyMap[$sort] ?? 'time';
  usort($logs, function($a, $b) use ($k, $dir) {
    $va = $a[$k] ?? '';
    $vb = $b[$k] ?? '';
    if (is_numeric($va) && is_numeric($vb)) {
      $cmp = $va <=> $vb;
    } else {
      $cmp = strcasecmp((string)$va, (string)$vb);
    }
    return ($dir === 'desc') ? -$cmp : $cmp;
  });

  // Print-friendly HTML (use browser's "Save as PDF")
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>Weather Report</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 24px; color:#222; }
      h2 { margin:0 0 16px; }
      .meta { margin-bottom: 10px; font-size: 14px; color:#555; }
      table { width:100%; border-collapse: collapse; }
      th, td { border:1px solid #999; padding:8px 10px; font-size: 13px; text-align:center; }
      th { background:#eee; }
      @media print {
        .noprint { display:none; }
      }
    </style>
  </head>
  <body>
    <div class="noprint" style="margin-bottom:10px;">
      <button onclick="window.print()">Print / Save as PDF</button>
    </div>
    <h2>Weather Report</h2>
    <div class="meta">Exported: <?php date_default_timezone_set('Asia/Kolkata'); echo date('Y-m-d H:i:s'); ?> (IST)</div>
    <table>
      <thead>
        <tr>
          <th>City</th>
          <th>Temperature (°C)</th>
          <th>Condition</th>
          <th>Humidity (%)</th>
          <th>Wind (m/s)</th>
          <th>Time (IST)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['city'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['temp'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['desc'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['humidity'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['wind'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['time'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <script>window.onload = () => setTimeout(()=>window.print(), 400);</script>
  </body>
  </html>
  <?php
  exit;
}

// ---------- Load + prepare data for UI ----------
$logs = read_logs($logFile);

// Preserve original index for delete action
foreach ($logs as $i => &$row) {
  $row['_idx'] = $i;
}
unset($row);

// Sorting (UI)
$allowedSort = ['city','temp','desc','humidity','wind','time'];
$sort = $_GET['sort'] ?? 'time';
$dir  = strtolower($_GET['dir'] ?? 'desc');
if (!in_array($sort, $allowedSort)) $sort = 'time';
if (!in_array($dir, ['asc','desc'])) $dir = 'desc';

usort($logs, function($a, $b) use ($sort, $dir) {
  $va = $a[$sort] ?? '';
  $vb = $b[$sort] ?? '';
  if (is_numeric($va) && is_numeric($vb)) {
    $cmp = $va <=> $vb;
  } else {
    $cmp = strcasecmp((string)$va, (string)$vb);
  }
  return ($dir === 'desc') ? -$cmp : $cmp;
});

// Pagination
$per = max(5, min(50, (int)($_GET['per'] ?? 10)));
$total = count($logs);
$pages = max(1, (int)ceil($total / $per));
$page  = max(1, min($pages, (int)($_GET['page'] ?? 1)));
$start = ($page - 1) * $per;
$view  = array_slice($logs, $start, $per);

// Helper to build query
function q($arr = []) {
  $base = $_GET;
  foreach ($arr as $k => $v) {
    if ($v === null) unset($base[$k]);
    else $base[$k] = $v;
  }
  return 'admin.php?' . http_build_query($base);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', sans-serif; background: #eef2f7; margin: 0; }
    a { text-decoration: none; }

    /* Fixed Header */
    .header {
      position: sticky;
      top: 0;
      z-index: 20;
      background: #1565c0;
      color: #fff;
      padding: 14px 18px;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    .header h2 { margin: 0; font-weight: 600; }
    .header .links a {
      color: #fff; margin-left: 14px; font-weight: 600; padding: 8px 12px; border-radius: 8px;
    }
    .header .links a:hover { background: rgba(255,255,255,0.15); }

    .container {
      max-width: 1100px; margin: 24px auto; background: #fff; border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      padding: 18px;
    }

    .toolbar {
      display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;
      margin-bottom: 12px;
    }
    .left-actions, .right-actions { display: flex; gap: 10px; align-items: center; }

    .btn {
      border: 0; padding: 10px 14px; border-radius: 8px; cursor: pointer; font-weight: 600;
    }
    .btn-secondary { background: #eeeeee; }
    .btn-blue { background:#1976d2; color:#fff; }
    .btn-red { background:#e53935; color:#fff; }
    .btn-green { background:#2e7d32; color:#fff; }

    select, .per-select {
      padding: 8px 10px; border:1px solid #d0d7e2; border-radius:8px; background:#f7f9fc;
    }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 12px; border-bottom: 1px solid #eef1f6; text-align: center; }
    th {
      background: #f5f7fb; position: sticky; top: 56px; z-index: 10;
      font-size: 14px;
    }
    tr:hover { background: #fafcff; }

    /* Row tones (by temperature) */
    .row-hot  { background: #fff5f5; }
    .row-cold { background: #f3f8ff; }
    .row-mild { background: #f9fbff; }

    /* Condition chips */
    .chip {
      display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border-radius: 999px;
      font-weight: 600; font-size: 12px;
    }
    .cond-clear { background:#fff8e1; color:#b26a00; }
    .cond-cloud { background:#eceff1; color:#455a64; }
    .cond-rain  { background:#e3f2fd; color:#1565c0; }
    .cond-storm { background:#ede7f6; color:#5e35b1; }
    .cond-snow  { background:#e1f5fe; color:#0277bd; }
    .cond-haze  { background:#f3e5f5; color:#6a1b9a; }
    .cond-other { background:#e8f5e9; color:#1b5e20; }

    .del-btn {
      background:#ffebee; color:#c62828; border:1px solid #ef9a9a; padding:6px 10px; border-radius:8px; cursor:pointer;
    }
    .del-btn:hover { background:#ffcdd2; }

    .pagination { display:flex; gap:8px; justify-content:center; margin-top: 14px; flex-wrap: wrap; }
    .page-link {
      padding:8px 12px; border:1px solid #d0d7e2; border-radius:8px; background:#fff; color:#1976d2; font-weight:600;
    }
    .page-link.active { background:#1976d2; color:#fff; }

    .login-card { max-width: 420px; margin: 60px auto; background:#fff; padding: 24px; border-radius: 12px; box-shadow:0 10px 22px rgba(0,0,0,0.08); }
    input[type="email"], input[type="password"] {
      width: 100%; padding: 12px; margin: 10px 0; border:1px solid #d0d7e2; border-radius: 8px; font-size: 15px; background:#f7f9fc;
    }
    .error { color:#e53935; margin-top:8px; }

    .muted { color:#607d8b; font-size: 12px; }

    /* Make table horizontally scrollable on small screens */
    .table-wrap { overflow-x: auto; }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
  <div class="login-card">
    <h2>🔐 Admin Login</h2>
    <form method="post">
      <input type="email" name="email" placeholder="Enter email" required>
      <input type="password" name="password" placeholder="Enter password" required>
      <button class="btn btn-blue" type="submit" name="login">Login</button>
    </form>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <div class="muted">Tip: Session timeout ke baad dubara login karna hoga.</div>
  </div>

<?php else: ?>
  <div class="header">
    <h2>📊 Admin Dashboard</h2>
    <div class="links">
      <a href="index.php">🏠 Home</a>
      <a href="<?= q(['export'=>'csv']) ?>">⬇ Export CSV</a>
      <a href="<?= q(['export'=>'pdf']) ?>">🖨 Export PDF (Print)</a>
      <a href="?clear=true" onclick="return confirm('Clear ALL history?')">🗑 Clear History</a>
      <a href="?logout=true">🚪 Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="toolbar">
      <div class="left-actions">
        <span class="muted">Sorted by: <b><?= htmlspecialchars(strtoupper($sort)) ?></b> (<?= htmlspecialchars(strtoupper($dir)) ?>)</span>
      </div>
      <div class="right-actions">
        <label class="muted">Rows per page</label>
        <select onchange="location.href='<?= q(['per'=>null,'page'=>1]) ?>&per='+this.value" class="per-select">
          <?php foreach ([10,15,20,30,50] as $opt): ?>
            <option value="<?= $opt ?>" <?= $per==$opt?'selected':'' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <?php
              // helper for sortable headers
              function th_sort($label,$field,$sort,$dir){
                $newDir = ($sort===$field && $dir==='asc') ? 'desc' : 'asc';
                $arrow = '';
                if ($sort===$field) $arrow = $dir==='asc' ? '▲' : '▼';
                echo '<th><a href="'.htmlspecialchars(q(['sort'=>$field,'dir'=>$newDir,'page'=>1])).'">'.$label.' '.$arrow.'</a></th>';
              }
              th_sort('City','city',$sort,$dir);
              th_sort('Temperature (°C)','temp',$sort,$dir);
              th_sort('Condition','desc',$sort,$dir);
              th_sort('Humidity (%)','humidity',$sort,$dir);
              th_sort('Wind (m/s)','wind',$sort,$dir);
              th_sort('Time (IST)','time',$sort,$dir);
            ?>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($view)): ?>
            <tr><td colspan="7" style="padding:18px;">No weather history available.</td></tr>
          <?php else: ?>
            <?php foreach ($view as $row): 
              $tclass = temp_class($row['temp'] ?? null);
              $cclass = condition_class($row['desc'] ?? '');
              $icon   = condition_icon($row['desc'] ?? '');
            ?>
              <tr class="<?= $tclass ?>">
                <td><?= htmlspecialchars($row['city'] ?? '') ?></td>
                <td><b><?= htmlspecialchars($row['temp'] ?? '') ?></b></td>
                <td>
                  <span class="chip <?= $cclass ?>"><?= $icon ?> <?= htmlspecialchars(ucfirst($row['desc'] ?? '')) ?></span>
                </td>
                <td><?= htmlspecialchars($row['humidity'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['wind'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['time'] ?? '') ?></td>
                <td>
                  <a class="del-btn" href="<?= q(['action'=>'delete','idx'=>$row['_idx']]) ?>" onclick="return confirm('Delete this entry?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a class="page-link" href="<?= q(['page'=>1]) ?>">« First</a>
        <a class="page-link" href="<?= q(['page'=>$page-1]) ?>">‹ Prev</a>
      <?php endif; ?>

      <?php
        // windowed pages
        $win = 3;
        $from = max(1, $page-$win);
        $to   = min($pages, $page+$win);
        for ($p=$from; $p<=$to; $p++):
      ?>
        <a class="page-link <?= $p==$page?'active':'' ?>" href="<?= q(['page'=>$p]) ?>"><?= $p ?></a>
      <?php endfor; ?>

      <?php if ($page < $pages): ?>
        <a class="page-link" href="<?= q(['page'=>$page+1]) ?>">Next ›</a>
        <a class="page-link" href="<?= q(['page'=>$pages]) ?>">Last »</a>
      <?php endif; ?>
    </div>

    <div class="muted" style="margin-top:10px;">
         </div>
  </div>
<?php endif; ?>

</body>
</html>