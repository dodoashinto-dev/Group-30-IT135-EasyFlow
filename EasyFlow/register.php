<?php
require 'db.php';
$message = '';
$phoneNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedPhone = $_POST['phone_number'] ?? '';
    $submittedPassword = $_POST['password'] ?? '';
    $phoneNumber = is_string($submittedPhone)
        ? preg_replace('/[\s().-]/', '', trim($submittedPhone))
        : '';
    $pass = is_string($submittedPassword) ? $submittedPassword : '';
    $smsRemindersEnabled = isset($_POST['sms_reminders_enabled']) ? 1 : 0;

    if ($phoneNumber !== '' && $pass !== '' && preg_match('/^\+?[1-9]\d{7,14}$/', $phoneNumber)) {
        // Hash the password securely
        $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (phone_number, password_hash, sms_reminders_enabled) VALUES (?, ?, ?)");
            $stmt->execute([$phoneNumber, $hashedPassword, $smsRemindersEnabled]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $message = "That phone number is already registered. Please log in or use another number.";
        }
    } else {
        $message = "Enter a valid international phone number and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - EasyFlow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>
    <?php if ($message): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST">
        <label for="phone_number">Phone number (include country code)</label>
        <input type="tel" id="phone_number" name="phone_number" value="<?= htmlspecialchars($phoneNumber) ?>" placeholder="+233241234567" autocomplete="tel" pattern="\+?[1-9][0-9]{7,14}" required>
        <label>Password</label>
        <input type="password" name="password" autocomplete="new-password" required>
        <label class="checkbox-label">
            <input type="checkbox" name="sms_reminders_enabled" value="1">
            I agree to receive cycle reminders by SMS.
        </label>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>