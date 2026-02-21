<?php
// Configure PHP upload settings
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', '300');

require_once 'config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_vip']) {
    header('Location: dashboard.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username, email, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $upload_dir = 'uploads/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = $_FILES['document']['name'];
    $file_tmp = $_FILES['document']['tmp_name'];
    $file_size = $_FILES['document']['size'];
    
    // VULNERABLE: No proper file validation
    // Only checks file size, not extension or content
    if ($file_size > 5000000) {
        $error = 'File size exceeds 5MB limit';
    } else {
        $upload_path = $upload_dir . basename($file_name);
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $message = 'Document uploaded successfully!';
        } else {
            $error = 'Upload failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Portal - WolfCore Bank</title>
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
                <a href="transfer.php">Transfer</a>
                <a href="search.php">Services</a>
                <a href="vip.php" class="active">VIP Portal</a>
                <a href="logout.php">Logout</a>
            </div>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['username']); ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="vip-header">
            <h1>VIP Member Portal</h1>
            <span class="vip-badge-large">VIP</span>
        </div>

        <div class="vip-benefits">
            <h2>Your VIP Benefits</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <h3>Priority Support</h3>
                    <p>24/7 dedicated customer service</p>
                </div>
                <div class="benefit-card">
                    <h3>Higher Limits</h3>
                    <p>Increased transaction limits</p>
                </div>
                <div class="benefit-card">
                    <h3>Document Management</h3>
                    <p>Secure document upload system</p>
                </div>
                <div class="benefit-card">
                    <h3>Investment Access</h3>
                    <p>Exclusive investment opportunities</p>
                </div>
            </div>
        </div>

        <div class="document-upload-section">
            <h2>Document Management</h2>
            <p>Upload important documents securely to your account</p>

            <?php if ($message): ?>
                <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="vip.php" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="document">Select Document</label>
                    <input type="file" id="document" name="document" required>
                    <p class="form-hint">Maximum file size: 5MB</p>
                </div>

                <button type="submit" class="btn-primary">Upload Document</button>
            </form>
        </div>

        <div class="vip-account-info">
            <h2>Account Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Account Holder:</strong>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <strong>Current Balance:</strong>
                    <span>£<?php echo number_format($user['balance'], 2); ?></span>
                </div>
                <div class="info-item">
                    <strong>Membership Level:</strong>
                    <span class="vip-badge">VIP</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
