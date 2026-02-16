<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username, is_vip FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$search_results = [];
$search_query = '';

if (isset($_GET['query'])) {
    $search_query = $_GET['query'];
    
    // VULNERABLE: SQL Injection - directly concatenating user input
    $sql = "SELECT * FROM services WHERE service_name LIKE '%" . $search_query . "%' OR description LIKE '%" . $search_query . "%' OR category LIKE '%" . $search_query . "%'";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
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
    <title>Services - WolfCore Bank</title>
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
                <a href="search.php" class="active">Services</a>
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
        <h1>Search Our Services</h1>
        
        <div class="search-container">
            <form method="GET" action="search.php">
                <div class="search-box">
                    <input type="text" name="query" placeholder="Search for banking services..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn-primary">Search</button>
                </div>
            </form>
        </div>

        <?php if ($search_query): ?>
            <div class="search-results">
                <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
                
                <?php if (count($search_results) > 0): ?>
                    <div class="services-grid">
                        <?php foreach ($search_results as $service): ?>
                            <div class="service-card">
                                <?php if (isset($service['service_name'])): ?>
                                    <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                                    <p class="category"><?php echo htmlspecialchars($service['category']); ?></p>
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <?php else: ?>
                                    <!-- Display any other data returned from SQL injection -->
                                    <div class="data-row">
                                        <?php foreach ($service as $key => $value): ?>
                                            <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-results">No results found</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="services-info">
                <h2>Our Banking Services</h2>
                <p>Use the search above to find information about our comprehensive range of banking services including accounts, loans, credit cards, and investment options.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
