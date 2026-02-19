<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

session_write_close();

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT username, email, balance, is_vip FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $to_username = trim($_POST['to_username']);
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Invalid amount.";
    } else {

        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $current_balance = $stmt->get_result()->fetch_assoc()['balance'];
        $stmt->close();

        if ($current_balance >= $amount) {

            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $to_username);
            $stmt->execute();
            $recipient = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($recipient) {

                usleep(700000);

                $new_balance = $current_balance - $amount;

                $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE username = ?");
                $stmt->bind_param("ds", $amount, $to_username);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO transactions (from_user_id, to_username, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $_SESSION['user_id'], $to_username, $amount);
                $stmt->execute();
                $stmt->close();

                if ($new_balance >= 1000 && !$user['is_vip']) {
                    $stmt = $conn->prepare("UPDATE users SET is_vip = TRUE WHERE id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['is_vip'] = true;
                    $message = "Transfer successful! You are now a VIP member!";
                } else {
                    $message = "Transfer successful!";
                }

                $user['balance'] = $new_balance;

            } else {
                $error = "Recipient not found.";
            }

        } else {
            $error = "Insufficient balance.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer - WolfCore CTF Bank</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="bank-logo-small">
            <span>🐺 WolfCore CTF Bank</span>
        </div>

        <div class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="transfer.php" class="active">Transfer</a>
            <a href="search.php">Services</a>
            <?php if ($user['is_vip']): ?>
                <a href="vip.php">VIP Portal</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>

        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($user['username']); ?>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Transfer Money</h1>

    <div class="transfer-container">

        <div class="balance-display">
            <h3>Available Balance</h3>
            <p class="balance">£<?php echo number_format($user['balance'], 2); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="transfer.php" class="transfer-form">
            <div class="form-group">
                <label for="to_username">Recipient Username</label>
                <input type="text" id="to_username" name="to_username" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount (£)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
            </div>

            <button type="submit" class="btn-primary">Transfer Money</button>
        </form>

        <div class="transfer-info">
            <h3>Transfer Information</h3>
            <ul>
                <li>Transfers are processed instantly</li>
                <li>No fees for internal transfers</li>
                <li>VIP membership available for accounts with £1000+ balance</li>
                <li>All transfers are final and logged</li>
            </ul>
        </div>

    </div>
</div>

</body>
</html>
