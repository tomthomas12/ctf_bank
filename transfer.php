<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username, email, balance, is_vip FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to_username = trim($_POST['to_username']);
    $amount = floatval($_POST['amount']);
    
    if ($amount <= 0) {
        $error = 'Invalid amount';
    } else {
        // VULNERABLE: Race Condition - No proper locking mechanism
        // Check balance (this creates a time window for race condition)
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $current_balance = $stmt->get_result()->fetch_assoc()['balance'];
        $stmt->close();
        
        if ($current_balance >= $amount) {
            // Verify recipient exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $to_username);
            $stmt->execute();
            $recipient = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($recipient) {
                // Sleep to make race condition easier to exploit
                usleep(100000); // 0.1 second delay
                
                // Deduct from sender
                $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
                
                // Add to recipient
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE username = ?");
                $stmt->bind_param("ds", $amount, $to_username);
                $stmt->execute();
                $stmt->close();
                
                // Record transaction
                $stmt = $conn->prepare("INSERT INTO transactions (from_user_id, to_username, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $_SESSION['user_id'], $to_username, $amount);
                $stmt->execute();
                $stmt->close();
                
                // Check if user should become VIP (balance >= 1000)
                $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $new_balance = $stmt->get_result()->fetch_assoc()['balance'];
                $stmt->close();
                
                if ($new_balance >= 1000 && !$user['is_vip']) {
                    $stmt = $conn->prepare("UPDATE users SET is_vip = TRUE WHERE id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['is_vip'] = true;
                    $message = 'Transfer successful! Congratulations, you are now a VIP member!';
                } else {
                    $message = 'Transfer successful!';
                }
                
                // Refresh user balance
                $user['balance'] = $new_balance;
            } else {
                $error = 'Recipient not found';
            }
        } else {
            $error = 'Insufficient balance';
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
    <title>Transfer - WolfCore Bank</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="bank-logo-small">
                <svg width="30" height="30" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25 5L45 15V20H5V15L25 5Z" fill="#ffffff"/>
                    <rect x="8" y="22" width="6" height="20" fill="#ffffff"/>
                    <rect x="17" y="22" width="6" height="20" fill="#ffffff"/>
                    <rect x="26" y="22" width="6" height="20" fill="#ffffff"/>
                    <rect x="35" y="22" width="6" height="20" fill="#ffffff"/>
                    <rect x="5" y="42" width="40" height="3" fill="#ffffff"/>
                </svg>
                <span>WolfCore CTF Bank</span>
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
                <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
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
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
