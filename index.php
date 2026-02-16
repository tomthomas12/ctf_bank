<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, is_vip FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_vip'] = $user['is_vip'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WolfCore Bank - Secure Online Banking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="bank-logo">
                <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25 5L45 15V20H5V15L25 5Z" fill="#1a5490"/>
                    <rect x="8" y="22" width="6" height="20" fill="#1a5490"/>
                    <rect x="17" y="22" width="6" height="20" fill="#1a5490"/>
                    <rect x="26" y="22" width="6" height="20" fill="#1a5490"/>
                    <rect x="35" y="22" width="6" height="20" fill="#1a5490"/>
                    <rect x="5" y="42" width="40" height="3" fill="#1a5490"/>
                </svg>
                <h1>WolfCore CTF Bank</h1>
            </div>
            <p class="tagline">Trusted Banking Solutions Since 1995</p>
        </div>
        
        <div class="login-box">
            <h2>Sign In to Your Account</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-primary">Sign In</button>
            </form>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
        </div>

    </div>
</body>
</html>
