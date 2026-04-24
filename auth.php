<?php
// =============================================================
//  CATERING SERVICE SYSTEM — Authentication
//  File: auth.php
// =============================================================
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------
// Login  (returns true on success, error string on failure)
// ---------------------------------------------------------------
function login(string $username, string $password): bool|string {
    $user = dbFetchOne(
        'SELECT id, username, password_hash, full_name, email, role FROM users WHERE username = ? OR email = ?',
        [$username, $username]
    );

    if (!$user) {
        return 'Invalid username or password.';
    }

    if (!password_verify($password, $user['password_hash'])) {
        return 'Invalid username or password.';
    }

    // Persist session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
    session_regenerate_id(true);

    return true;
}

// ---------------------------------------------------------------
// Client login
// ---------------------------------------------------------------
function clientLogin(string $email, string $password): bool|string {
    $client = dbFetchOne(
        'SELECT id, full_name, email, password_hash FROM clients WHERE email = ?',
        [$email]
    );

    if (!$client || !password_verify($password, $client['password_hash'])) {
        return 'Invalid email or password.';
    }

    $_SESSION['client_id']   = $client['id'];
    $_SESSION['client_name'] = $client['full_name'];
    session_regenerate_id(true);

    return true;
}

// ---------------------------------------------------------------
// Client Registration
// ---------------------------------------------------------------
function registerClient(string $fullName, string $email, string $password): bool|string {
    $existing = dbFetchOne('SELECT id FROM clients WHERE email = ?', [$email]);
    if ($existing) {
        return 'Email is already registered.';
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $affected = dbExecute(
        'INSERT INTO clients (full_name, email, password_hash) VALUES (?, ?, ?)',
        [$fullName, $email, $hash]
    );

    if ($affected !== 1) {
        return 'Failed to create account. Please try again.';
    }

    $clientId = dbLastId();

    $_SESSION['client_id']   = $clientId;
    $_SESSION['client_name'] = $fullName;
    session_regenerate_id(true);

    return true;
}

// ---------------------------------------------------------------
// Logout
// ---------------------------------------------------------------
function logout(): void {
    $isStaff = !empty($_SESSION['user_id']);
    session_unset();
    session_destroy();
    if ($isStaff) {
        header('Location: login_staff.php');
    } else {
        header('Location: login_client.php');
    }
    exit;
}

// ---------------------------------------------------------------
// Guards
// ---------------------------------------------------------------
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login_staff.php');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die('<h2>403 — Access Denied</h2>');
    }
}

function requireClientLogin(): void {
    if (empty($_SESSION['client_id'])) {
        header('Location: login_client.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) || !empty($_SESSION['client_id']);
}

function currentUser(): array {
    return [
        'id'        => $_SESSION['user_id']   ?? null,
        'username'  => $_SESSION['username']  ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'email'     => $_SESSION['email']     ?? null,
        'role'      => $_SESSION['role']      ?? null,
    ];
}

// ---------------------------------------------------------------
// Login page handler (called when login.php is POSTed)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $accountType = $_POST['account_type'] ?? 'client';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Please fill in all fields.';
        } else {
            if ($accountType === 'staff') {
                $result = login($username, $password);
                if ($result === true) {
                    header('Location: admindash.php');
                    exit;
                }
                $_SESSION['login_error'] = $result;
                header('Location: login_staff.php');
                exit;
            } else {
                $result = clientLogin($username, $password);
                if ($result === true) {
                    header('Location: clientdash.php');
                    exit;
                }
                $_SESSION['login_error'] = $result;
                header('Location: login_client.php');
                exit;
            }
        }
    }

    if ($_POST['action'] === 'register') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($fullName === '' || $email === '' || $password === '') {
            $_SESSION['login_error'] = 'Please fill in all registration fields.';
        } else {
            $result = registerClient($fullName, $email, $password);
            if ($result === true) {
                header('Location: clientdash.php');
                exit;
            }
            $_SESSION['login_error'] = $result;
        }
        $referer = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'signup.php') !== false ? 'signup.php' : 'login.php';
        header("Location: $referer");
        exit;
    }

    if ($_POST['action'] === 'logout') {
        logout();
    }
}
