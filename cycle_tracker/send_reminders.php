<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/arkesel.php';

$reminderDate = (new DateTimeImmutable('today'))->modify('+3 days')->format('Y-m-d');
$stmt = $pdo->prepare(
    'SELECT c.id, c.next_period_date, u.phone_number
     FROM cycles c
     JOIN users u ON u.id = c.user_id
     WHERE c.next_period_date = ?
       AND c.id = (
           SELECT c2.id
           FROM cycles c2
           WHERE c2.user_id = c.user_id
           ORDER BY c2.created_at DESC, c2.id DESC
           LIMIT 1
       )
       AND u.sms_reminders_enabled = 1
       AND u.phone_number IS NOT NULL'
);
$stmt->execute([$reminderDate]);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$claimReminder = $pdo->prepare(
    "INSERT IGNORE INTO sms_reminder_log (cycle_id, reminder_type, status)
     VALUES (?, 'period_3_days', 'sending')"
);
$markSent = $pdo->prepare(
    "UPDATE sms_reminder_log
     SET status = 'sent', sent_at = CURRENT_TIMESTAMP
     WHERE cycle_id = ? AND reminder_type = 'period_3_days'"
);
$releaseReminder = $pdo->prepare(
    "DELETE FROM sms_reminder_log
     WHERE cycle_id = ? AND reminder_type = 'period_3_days' AND status = 'sending'"
);

foreach ($reminders as $reminder) {
    $claimReminder->execute([$reminder['id']]);
    if ($claimReminder->rowCount() === 0) {
        continue;
    }

    $date = (new DateTimeImmutable($reminder['next_period_date']))->format('M j');
    $message = "Cycle Tracker reminder: your next period is expected on {$date}. Dates are estimates.";

    try {
        sendArkeselSms($reminder['phone_number'], $message);
    } catch (RuntimeException $e) {
        $releaseReminder->execute([$reminder['id']]);
        fwrite(STDERR, "SMS failed for cycle {$reminder['id']}: {$e->getMessage()}\n");
        exit(1);
    }

    $markSent->execute([$reminder['id']]);
    echo "Sent period reminder for cycle {$reminder['id']}.\n";
}

echo 'Reminder run complete. ' . count($reminders) . " candidate(s) found.\n";
