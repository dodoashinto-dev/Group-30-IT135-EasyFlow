<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$displayName = $_SESSION['phone_number'] ?? $_SESSION['username'] ?? '';
$latest = null;
$show_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'calculate') {
        $last_period = $_POST['last_period'];
        $cycle_length = (int)$_POST['cycle_length'];
        $period_duration = (int)$_POST['period_duration'];

        $startDate = new DateTime($last_period);
        
        $nextPeriod = clone $startDate;
        $nextPeriod->modify("+{$cycle_length} days");

        $ovulation = clone $nextPeriod;
        $ovulation->modify("-14 days");

        $fertileStart = clone $ovulation;
        $fertileStart->modify("-5 days");
        $fertileEnd = clone $ovulation;

        $stmt = $pdo->prepare("INSERT INTO cycles 
            (user_id, last_period_date, cycle_length, period_duration, next_period_date, ovulation_date, fertile_start, fertile_end) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $user_id,
            $startDate->format('Y-m-d'),
            $cycle_length,
            $period_duration,
            $nextPeriod->format('Y-m-d'),
            $ovulation->format('Y-m-d'),
            $fertileStart->format('Y-m-d'),
            $fertileEnd->format('Y-m-d')
        ]);
        
        // Redirect to prevent form resubmission on refresh and hide form
        header("Location: index.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM cycles WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
$latest = $history[0] ?? null;

// If there's no history yet, force the form to show
if (!$latest) {
    $show_form = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cycle Tracker</title>
    <link rel="stylesheet" href="style.css?v=4">
    <script>
        function toggleForm() {
            const formCard = document.getElementById('calcFormCard');
            const btn = document.getElementById('toggleBtn');
            if (formCard.style.display === 'none' || formCard.style.display === '') {
                formCard.style.display = 'block';
                btn.innerHTML = '✖ Close Form';
            } else {
                formCard.style.display = 'none';
                btn.innerHTML = '➕ Log New Cycle';
            }
        }
    </script>
    <style>
        /* Extra inline helper for smooth toggle behavior */
        .form-card-container {
            display: <?= $show_form ? 'block' : 'none' ?>;
            margin-bottom: 25px;
            animation: fadeUp 0.4s ease-out;
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-toggle {
            background: #fce4ec;
            color: #d63384;
            border: none;
            padding: 10px 18px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .btn-toggle:hover {
            background: #d63384;
            color: #fff;
            transform: translateY(-2px);
        }
        .nav-links a {
            text-decoration: none;
            color: #636e72;
            font-weight: 600;
            font-size: 14px;
            margin-right: 15px;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: #d63384;
        }
    </style>
</head>
<body>
<div class="container wide">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2>🌸 Hi, <?= htmlspecialchars($displayName) ?></h2>
            <div class="nav-links">
                <a href="index.php">🏠 Dashboard</a>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <?php if ($latest): ?>
    <div class="action-bar">
        <h3>Your Tracker Overview</h3>
        <button id="toggleBtn" class="btn-toggle" onclick="toggleForm()">➕ Log New Cycle</button>
    </div>
    <?php endif; ?>

    <!-- Collapsible Form Card -->
    <div id="calcFormCard" class="form-card-container">
        <form method="POST">
            <input type="hidden" name="action" value="calculate">
            <h3>✨ Log & Calculate Cycle</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Day of Last Period</label>
                    <input type="date" name="last_period" required>
                </div>
                <div class="form-group">
                    <label>Average Cycle Length (Days)</label>
                    <input type="number" id="cycle_length" name="cycle_length" value="28" min="20" max="45" required>
                    <div class="quick-pills">
                        <button type="button" class="pill" onclick="document.getElementById('cycle_length').value=26">26d</button>
                        <button type="button" class="pill" onclick="document.getElementById('cycle_length').value=28">28d</button>
                        <button type="button" class="pill" onclick="document.getElementById('cycle_length').value=30">30d</button>
                        <button type="button" class="pill" onclick="document.getElementById('cycle_length').value=32">32d</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Period Duration (Days)</label>
                    <input type="number" id="period_duration" name="period_duration" value="5" min="2" max="10" required>
                    <div class="quick-pills">
                        <button type="button" class="pill" onclick="document.getElementById('period_duration').value=4">4d</button>
                        <button type="button" class="pill" onclick="document.getElementById('period_duration').value=5">5d</button>
                        <button type="button" class="pill" onclick="document.getElementById('period_duration').value=6">6d</button>
                        <button type="button" class="pill" onclick="document.getElementById('period_duration').value=7">7d</button>
                    </div>
                </div>
            </div>
            <button type="submit">Calculate & Save Cycle</button>
        </form>
    </div>

    <?php if ($latest): 
        $today = new DateTime();
        $nextP = new DateTime($latest['next_period_date']);
        $daysLeft = (int)$today->diff($nextP)->format('%r%a');
        $countdownText = $daysLeft > 0 ? "In {$daysLeft} days" : ($daysLeft === 0 ? "Expected today" : abs($daysLeft) . " days ago");

        $free1Start = (new DateTime($latest['last_period_date']))->modify("+{$latest['period_duration']} days")->format('M d');
        $free1End = (new DateTime($latest['fertile_start']))->modify("-1 day")->format('M d');
        $free2Start = (new DateTime($latest['fertile_end']))->modify("+1 day")->format('M d');
        $free2End = (new DateTime($latest['next_period_date']))->modify("-1 day")->format('M d');
    ?>
    <div class="prediction-section">
        <h3>📊 Your Latest Cycle Forecast</h3>
        <div class="cards-grid">
            <div class="stat-card card-period">
                <div class="title">🩸 Next Period</div>
                <div class="value"><?= date('M d, Y', strtotime($latest['next_period_date'])) ?></div>
                <div class="sub"><?= $countdownText ?></div>
            </div>
            <div class="stat-card card-ovulation">
                <div class="title">🥚 Ovulation Day</div>
                <div class="value"><?= date('M d, Y', strtotime($latest['ovulation_date'])) ?></div>
                <div class="sub">Peak fertility</div>
            </div>
            <div class="stat-card card-fertile">
                <div class="title">🔥 Fertile Window</div>
                <div class="value"><?= date('M d', strtotime($latest['fertile_start'])) ?> – <?= date('M d', strtotime($latest['fertile_end'])) ?></div>
                <div class="sub">High chance window</div>
            </div>
            <div class="stat-card card-safe">
                <div class="title">🌿 Safe / Free Days</div>
                <div class="value"><?= $free1Start ?>–<?= $free1End ?> & <?= $free2Start ?>–<?= $free2End ?></div>
                <div class="sub">Lower fertility days</div>
            </div>
        </div>
        <span class="disclaimer">*Note: Biological cycles vary naturally. These dates are estimates based on standard averages.</span>
    </div>
    <?php endif; ?>

    <?php if ($history): ?>
    <div class="history-section">
        <h3>🗓️ Saved History</h3>
        <table>
            <thead>
                <tr>
                    <th>Last Period</th>
                    <th>Next Period</th>
                    <th>Ovulation</th>
                    <th>Fertile Window</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['last_period_date'])) ?></td>
                    <td><strong><?= date('M d, Y', strtotime($row['next_period_date'])) ?></strong></td>
                    <td><?= date('M d, Y', strtotime($row['ovulation_date'])) ?></td>
                    <td><?= date('M d', strtotime($row['fertile_start'])) ?> – <?= date('M d, Y', strtotime($row['fertile_end'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>