<?php
session_start();
require 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedPhone = $_POST['phone_number'] ?? '';
    $submittedPassword = $_POST['password'] ?? '';
    $phoneNumber = is_string($submittedPhone)
        ? preg_replace('/[\s().-]/', '', trim($submittedPhone))
        : '';
    $pass = is_string($submittedPassword) ? $submittedPassword : '';

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE phone_number = ?");
    $stmt->execute([$phoneNumber]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account && password_verify($pass, $account['password_hash'])) {
        $_SESSION['user_id'] = $account['id'];
        $_SESSION['phone_number'] = $phoneNumber;
        header("Location: index.php");
        exit;
    } else {
        $message = "Invalid phone number or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - EasyFlow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if (isset($_GET['registered'])): ?><p class="success">Account created! Please log in.</p><?php endif; ?>
    <?php if ($message): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST">
        <label for="phone_number">Phone number</label>
        <input type="tel" id="phone_number" name="phone_number" placeholder="+233241234567" autocomplete="tel" pattern="\+?[1-9][0-9]{7,14}" required>
        <label>Password</label>
        <input type="password" name="password" autocomplete="current-password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>