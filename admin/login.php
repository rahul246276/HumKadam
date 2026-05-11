<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Create connection
$conn = getDBConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $admin_password = trim($_POST['password'] ?? '');

    // Fetch admin from DB
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // Verify password (supports both bcrypt hashes and legacy plain-text fallback)
    $valid = false;
    if ($admin) {
        if (password_verify($admin_password, $admin['password'])) {
            $valid = true;
        } elseif ($admin['password'] === $admin_password) {
            // Legacy plain-text — accept but upgrade to hash
            $hash = password_hash($admin_password, PASSWORD_BCRYPT);
            $upd = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hash, $admin['id']);
            $upd->execute();
            $upd->close();
            $valid = true;
        }
    }

    if ($valid) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        // Update last login
        $upd = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $upd->bind_param("i", $admin['id']);
        $upd->execute();
        $upd->close();
        header('Location: index.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - HumKadam</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: var(--maroon);
            margin-bottom: 10px;
        }
        .login-header p {
            color: var(--text-light);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--beige);
            border-radius: var(--radius-sm);
            font-size: 1rem;
        }
        .form-group input:focus {
            border-color: var(--gold);
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--maroon);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .login-btn:hover {
            background: var(--maroon-light);
        }
        .error {
            background: var(--red);
            color: white;
            padding: 10px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1><i class="fas fa-crown"></i> Admin Login</h1>
                <p>HumKadam Contact Management System</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>