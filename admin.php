<?php
session_start();

// Database configuration
define('DB_HOST', 'fdb1034.awardspace.net');
define('DB_USER', '4669157_educ');
define('DB_PASS', '1lovemyself');
define('DB_NAME', '4669157_educ');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$username = $password = $confirmPassword = $accessPassword = "";
$error = $success = "";
$currentSection = "access"; // access, login, register, dashboard, manage_users

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $currentSection = "dashboard";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Admin access verification
    if (isset($_POST['access_password'])) {
        $accessPassword = trim($_POST['access_password']);
        
        // Default access password is "admin123"
        if ($accessPassword === "admin123") {
            $currentSection = "login";
        } else {
            $error = "Incorrect admin access password.";
            $currentSection = "access";
        }
    }
    
    // Admin login
    if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
        $username = trim($_POST['login_username']);
        $password = $_POST['login_password'];
        
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $currentSection = "dashboard";
                $success = "Login successful! Welcome back, " . htmlspecialchars($admin['username']) . ".";
            } else {
                $error = "Invalid password.";
                $currentSection = "login";
            }
        } else {
            $error = "Username not found.";
            $currentSection = "login";
        }
    }
    
    // Admin registration
    if (isset($_POST['register_username']) && isset($_POST['register_password']) && isset($_POST['confirm_password'])) {
        $username = trim($_POST['register_username']);
        $password = $_POST['register_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate input
        if (empty($username) || empty($password) || empty($confirmPassword)) {
            $error = 'All fields are required';
            $currentSection = "register";
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
            $currentSection = "register";
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
            $currentSection = "register";
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username already exists';
                $currentSection = "register";
            } else {
                // Hash password and create admin
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                
                if ($stmt->execute([$username, $hashedPassword])) {
                    $success = 'Registration successful! You can now login.';
                    $currentSection = "login";
                    // Clear form fields
                    $username = $password = $confirmPassword = "";
                } else {
                    $error = 'Registration failed. Please try again.';
                    $currentSection = "register";
                }
            }
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $success = "User deleted successfully!";
            } else {
                $error = "Failed to delete user.";
            }
        } catch(PDOException $e) {
            $error = "Error deleting user: " . $e->getMessage();
        }
        
        $currentSection = "manage_users";
    }
    
    // Logout
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        $currentSection = "access";
        $success = "You have been logged out successfully.";
    }
    
    // Navigation requests
    if (isset($_POST['navigate_to'])) {
        $currentSection = $_POST['navigate_to'];
    }
}

// Get all users for management
$users = [];
if ($currentSection === "manage_users") {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error = "Error fetching users: " . $e->getMessage();
    }
}

// Handle theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
if (isset($_POST['toggle_theme'])) {
    $theme = $theme === 'dark' ? 'light' : 'dark';
    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), '/'); // 30 days
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System | Secure Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #4D44DB;
            --accent: #FF6584;
            --dark: #2D3748;
            --light: #F7FAFC;
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FF9800;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --dark-text: #e6e6e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-bg), #0f3460);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            transition: background 0.3s ease;
        }
        
        body[data-theme="light"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--dark);
        }
        
        .container {
            width: 100%;
            max-width: 900px;
            background: var(--dark-card);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
            transition: background 0.3s ease;
        }
        
        body[data-theme="light"] .container {
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 10px 0 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark-text);
            transition: color 0.3s ease;
        }
        
        body[data-theme="light"] .form-label {
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--dark-text);
        }
        
        body[data-theme="light"] .form-input {
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            color: var(--dark);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-full {
            width: 100%;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #d32f2f);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #2e7d32);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
        
        .alert-warning {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.2);
        }
        
        .toggle-form {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--dark-text);
            transition: color 0.3s ease;
        }
        
        body[data-theme="light"] .toggle-form {
            color: var(--dark);
        }
        
        .toggle-form-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline;
        }
        
        .toggle-form-btn:hover {
            color: var(--secondary);
        }
        
        .hidden {
            display: none;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .password-strength {
            height: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        body[data-theme="light"] .password-strength {
            background-color: #e2e8f0;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s;
        }
        
        .password-hint {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
            transition: color 0.3s ease;
        }
        
        body[data-theme="light"] .password-hint {
            color: #64748b;
        }
        
        .home-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .dashboard-container {
            padding: 20px;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .admin-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        body[data-theme="light"] .admin-info {
            background: #f8fafc;
        }
        
        .logout-form {
            text-align: center;
            margin-top: 20px;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        body[data-theme="light"] .users-table {
            background: #f8fafc;
        }
        
        body[data-theme="light"] .users-table th, 
        body[data-theme="light"] .users-table td {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .users-table th {
            background: rgba(108, 99, 255, 0.1);
            font-weight: 600;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .users-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        
        body[data-theme="light"] .users-table tr:hover {
            background: #f1f5f9;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        body[data-theme="light"] .empty-state {
            color: #64748b;
        }
        
        @media (max-width: 576px) {
            .container {
                border-radius: 12px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .admin-actions {
                grid-template-columns: 1fr;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body data-theme="<?php echo $theme; ?>">
    <div class="container">
        <div class="header">
            <button class="home-btn" onclick="window.location.href = 'index.html'">
                <i class="fas fa-arrow-left"></i> Home
            </button>
            
            <form method="POST" class="theme-toggle-form">
                <button type="submit" name="toggle_theme" class="theme-toggle">
                    <i class="fas fa-<?php echo $theme === 'dark' ? 'moon' : 'sun'; ?>"></i>
                </button>
            </form>
            
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="logo-text">EduSyncHub</div>
            </div>
            <h1>Admin Portal</h1>
            <p>Secure access to administration features</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Admin Access Section -->
            <div id="adminAccessSection" <?php if ($currentSection !== "access") echo 'class="hidden"'; ?>>
                <h2 style="text-align: center; margin-bottom: 20px; color: var(--primary);">Admin Access</h2>
                <p style="text-align: center; margin-bottom: 20px; color: var(--dark-text);">Enter the admin access password to continue</p>
                <form method="POST">
                    <div class="form-group">
                        <label for="accessPassword" class="form-label">Admin Access Password</label>
                        <input type="password" id="accessPassword" name="access_password" class="form-input" placeholder="Enter access password" required>
                    </div>
                    <button type="submit" class="btn btn-full"><i class="fas fa-key"></i> Verify Access</button>
                </form>
            </div>

            <!-- Admin Login Section -->
            <div id="adminLoginSection" <?php if ($currentSection !== "login") echo 'class="hidden"'; ?>>
                <h2 style="text-align: center; margin-bottom: 20px; color: var(--primary);">Admin Login</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="loginUsername" class="form-label">Username</label>
                        <input type="text" id="loginUsername" name="login_username" class="form-input" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" id="loginPassword" name="login_password" class="form-input" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-full"><i class="fas fa-sign-in-alt"></i> Login</button>
                </form>
                <div class="toggle-form">
                    <p>Don't have an account? 
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="navigate_to" value="register">
                            <button type="submit" class="toggle-form-btn">Register</button>
                        </form>
                    </p>
                </div>
            </div>

            <!-- Admin Registration Section -->
            <div id="adminRegisterSection" <?php if ($currentSection !== "register") echo 'class="hidden"'; ?>>
                <h2 style="text-align: center; margin-bottom: 20px; color: var(--primary);">Admin Registration</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="registerUsername" class="form-label">Username</label>
                        <input type="text" id="registerUsername" name="register_username" class="form-input" placeholder="Choose a username" required>
                    </div>
                    <div class="form-group">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" id="registerPassword" name="register_password" class="form-input" placeholder="Create a password" required oninput="updatePasswordStrength(this.value)">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-hint">Password must be at least 6 characters long</div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn btn-full"><i class="fas fa-user-plus"></i> Register</button>
                </form>
                <div class="toggle-form">
                    <p>Already have an account? 
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="navigate_to" value="login">
                            <button type="submit" class="toggle-form-btn">Login</button>
                        </form>
                    </p>
                </div>
            </div>

            <!-- Admin Dashboard Section -->
            <div id="adminDashboardSection" <?php if ($currentSection !== "dashboard") echo 'class="hidden"'; ?>>
                <div class="dashboard-container">
                    <h2 class="welcome-message">Welcome, <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?>!</h2>
                    
                    <div class="admin-info">
                        <h3><i class="fas fa-info-circle"></i> Admin Information</h3>
                        <p>Username: <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'N/A'; ?></p>
                        <p>Admin ID: <?php echo isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'N/A'; ?></p>
                        <p>Login Time: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    
                    <h3><i class="fas fa-cogs"></i> Admin Actions</h3>
                    <div class="admin-actions">
                        <form method="POST">
                            <input type="hidden" name="navigate_to" value="manage_users">
                            <button type="submit" class="action-btn">
                                <i class="fas fa-users"></i> Manage Users
                            </button>
                        </form>
                        <button class="action-btn" onclick="window.location.href = 'library.html'">
                            <i class="fas fa-file-alt"></i> Manage Content
                        </button>
                        <button class="action-btn" onclick="alert('Settings panel would open here')">
                            <i class="fas fa-cog"></i> Settings
                        </button>
                        <button class="action-btn" onclick="alert('Statistics would be displayed here')">
                            <i class="fas fa-chart-bar"></i> View Stats
                        </button>
                    </div>
                    
                    <form method="POST" class="logout-form">
                        <input type="hidden" name="logout" value="1">
                        <button type="submit" class="btn btn-full"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>

            <!-- Manage Users Section -->
            <div id="manageUsersSection" <?php if ($currentSection !== "manage_users") echo 'class="hidden"'; ?>>
                <div class="dashboard-container">
                    <form method="POST" class="back-btn">
                        <input type="hidden" name="navigate_to" value="dashboard">
                        <button type="submit" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</button>
                    </form>
                    
                    <h2 class="welcome-message"><i class="fas fa-users"></i> Manage Users</h2>
                    
                    <?php if (count($users) > 0): ?>
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="navigate_to" value="manage_users">
                                                <button type="submit" name="delete_user" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px;"></i>
                            <h3>No Users Found</h3>
                            <p>There are no users in the database.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        function updatePasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 1;
            if (password.length >= 10) strength += 1;
            
            // Contains numbers
            if (/\d/.test(password)) strength += 1;
            
            // Contains special chars
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Contains uppercase and lowercase
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Update strength bar
            const width = strength * 20;
            document.getElementById('passwordStrengthBar').style.width = `${width}%`;
            
            // Update color
            if (strength <= 1) {
                document.getElementById('passwordStrengthBar').style.backgroundColor = '#ef4444'; // red
            } else if (strength <= 3) {
                document.getElementById('passwordStrengthBar').style.backgroundColor = '#f59e0b'; // orange
            } else {
                document.getElementById('passwordStrengthBar').style.backgroundColor = '#10b981'; // green
            }
        }
        
        // Enter key support for forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>