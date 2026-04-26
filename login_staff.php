<?php
session_start();
// Include auth file if needed, but for now we just handle session errors
require_once 'auth.php';
if (isLoggedIn()) {
    if (!empty($_SESSION['user_id'])) {
        header('Location: admindash.php');
    } else {
        header('Location: clientdash.php');
    }
    exit;
}
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Management - Welcome</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d4af37; /* Elegant Gold */
            --primary-hover: #b5952f;
            --bg-overlay: rgba(20, 20, 22, 0.7);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-main: #ffffff;
            --text-muted: #cbd5e1;
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            background-image: url('assets/img/catering_bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-overlay);
            backdrop-filter: blur(8px);
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            padding: 2rem;
            perspective: 1000px;
        }

        .auth-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            color: var(--text-main);
            position: relative;
            overflow: hidden;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), #fcd34d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
        }

        .logo-icon svg {
            width: 32px;
            height: 32px;
            fill: #1a1a1a;
        }

        .logo-text {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 4px;
        }

        .tab {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--text-muted);
        }

        .tab.active {
            background: var(--primary);
            color: #1a1a1a;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .form-container {
            position: relative;
        }

        .form {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .form.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .input-field {
            position: relative;
        }

        .input-field input, .input-field select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .input-field select {
            appearance: none;
        }
        
        .input-field select option {
            background: #1a1a1a;
            color: white;
        }

        .input-field input:focus, .input-field select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 20px;
            height: 20px;
            transition: color 0.3s ease;
        }

        .input-field input:focus ~ .input-icon,
        .input-field select:focus ~ .input-icon {
            color: var(--primary);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: #1a1a1a;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--primary);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* SVG Icons */
        .icon-svg {
            width: 100%;
            height: 100%;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="auth-card" id="authCard">
            
            <div class="logo-container">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" stroke="none" fill="currentColor" opacity="0"/>
                        <path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm1 12h-2v-2h2v2zm0-4h-2V7h2v5z"/>
                        <!-- Decorative dining plate & fork/knife icon representation -->
                        <path stroke="none" fill="currentColor" d="M11 6h2v6h-2V6zm0 8h2v2h-2v-2zm-6.5 0a6.5 6.5 0 0 1 13 0h-13z" opacity="0"/>
                        <circle cx="12" cy="12" r="7" />
                        <path d="M12 9v3l2 2" />
                    </svg>
                </div>
                <h1 class="logo-text">Mashaal Catering System</h1>
            </div>

            <!-- No tabs for staff -->

            <?php if ($error): ?>
            <div class="error-message">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <!-- LOGIN FORM -->
                <form id="loginForm" class="form active" action="auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <input type="hidden" name="account_type" value="staff">
                    
                    <div class="input-group">
                        <label for="login-username">Username or Email</label>
                        <div class="input-field">
                            <input type="text" id="login-username" name="username" placeholder="Enter your username" required>
                            <div class="input-icon">
                                <svg class="icon-svg"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="login-password">Password</label>
                        <div class="input-field">
                            <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                            <div class="input-icon">
                                <svg class="icon-svg"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                    <a href="#" class="forgot-password">Forgot your password?</a>
                </form>

    <!-- No switchTab JS needed -->
</body>
</html>
