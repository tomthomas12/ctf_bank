<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();

// Get user information
$stmt = $conn->prepare("SELECT username, email, balance, is_vip FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get recent transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE from_user_id = ? ORDER BY transaction_date DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WolfCore Bank</title>
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="transfer.php">Transfer</a>
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
        <div class="dashboard-header">
            <h1>Account Overview</h1>
            <?php if ($user['is_vip']): ?>
                <span class="vip-badge">VIP Member</span>
            <?php endif; ?>
        </div>

        <div class="account-cards">
            <div class="account-card">
                <h3>Available Balance</h3>
                <p class="balance">£<?php echo number_format($user['balance'], 2); ?></p>
                <p class="account-number">Account: <?php echo str_pad($_SESSION['user_id'], 10, '0', STR_PAD_LEFT); ?></p>
            </div>

            <div class="account-card">
                <h3>Account Status</h3>
                <p class="status-active">Active</p>
                <p>Member Since: 2024</p>
            </div>

            <div class="account-card">
                <h3>Quick Actions</h3>
                <a href="transfer.php" class="btn-secondary">Transfer Money</a>
                <a href="search.php" class="btn-secondary">Browse Services</a>
            </div>
        </div>

        <div class="transactions-section">
            <h2>Recent Transactions</h2>
            <?php if ($transactions->num_rows > 0): ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Recipient</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['to_username']); ?></td>
                                <td class="amount-debit">-£<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td><span class="status-completed"><?php echo htmlspecialchars($transaction['status']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent transactions</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
