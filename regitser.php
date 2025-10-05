<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (!$name || strlen($name) < 3) {
        $error = 'Name must be at least 3 characters';
    } elseif (!$email) {
        $error = 'Please provide a valid email address';
    } elseif (!$phone) {
        $error = 'Phone number is required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = Database::getInstance();
            
            // Check if email already exists
            $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            
            if ($existing) {
                $error = 'Email already registered. Please login instead.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $userId = $db->insert('users', [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashedPassword,
                    'role' => 'user',
                    'status' => 'active'
                ]);
                
                // Send welcome email
                $subject = "Welcome to Iburunga Travel!";
                $message = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Welcome to Iburunga Travel!</h2>
                    <p>Hi $name,</p>
                    <p>Thank you for registering with us. Your account has been created successfully.</p>
                    <p><strong>Account Details:</strong></p>
                    <ul>
                        <li>Email: $email</li>
                        <li>Name: $name</li>
                    </ul>
                    <p>You can now browse and book our amazing tours.</p>
                    <p><a href='" . SITE_URL . "/login.php' style='display: inline-block; padding: 10px 20px; background: #2ECC71; color: white; text-decoration: none; border-radius: 5px;'>Login Now</a></p>
                    <p>Best regards,<br>Iburunga Travel Team</p>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
                
                mail($email, $subject, $message, $headers);
                
                $success = 'Registration successful! Please login to continue.';
                
                // Auto login
                $_SESSION['user_id'] = $userId;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                
                header("Location: index.php");
                exit();
            }
        } catch (Exception $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 2rem;
        }
        
        .auth-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header .logo {
            font-size: 3rem;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-group input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: 2px solid #DDD;
            border-radius: 10px;
            outline: none;
            transition: border 0.3s;
        }
        
        .input-group input:focus {
            border-color: var(--primary);
        }
        
        .error-message {
            background: #F8D7DA;
            color: #721C24;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #F5C6CB;
        }
        
        .success-message {
            background: #D4EDDA;
            color: #155724;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #C3E6CB;
        }
        
        .password-strength {
            height: 5px;
            background: #DDD;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo"><i class="fas fa-globe-africa"></i></div>
                <h1>Create Account</h1>
                <p>Join Iburunga Travel Community</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label>Full Name *</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" required placeholder="Enter your full name" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required placeholder="Enter your email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number *</label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" required placeholder="+250 788 123 456"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required 
                               placeholder="Create a password (min 6 characters)">
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small id="strengthText"></small>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" required 
                               placeholder="Confirm your password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" required>
                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
                <p style="margin-top: 1.5rem;"><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script>
    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let text = '';
        let color = '';
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                text = 'Weak';
                color = '#E74C3C';
                break;
            case 2:
            case 3:
                text = 'Medium';
                color = '#F39C12';
                break;
            case 4:
            case 5:
                text = 'Strong';
                color = '#27AE60';
                break;
        }
        
        strengthBar.style.width = (strength * 20) + '%';
        strengthBar.style.background = color;
        strengthText.textContent = password.length > 0 ? text : '';
        strengthText.style.color = color;
    });
    
    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.querySelector('[name="password"]').value;
        const confirm = document.querySelector('[name="confirm_password"]').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
    </script>
</body>
</html>
