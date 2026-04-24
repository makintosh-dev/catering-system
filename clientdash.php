<?php
session_start();
require_once 'auth.php';
requireClientLogin();

$clientId = $_SESSION['client_id'];
$clientName = $_SESSION['client_name'];
$page = $_GET['page'] ?? 'home';

// Initialize Cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- HANDLERS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectPage = $page;
    
    if ($action === 'update_profile') {
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        dbExecute('UPDATE clients SET full_name=?, email=?, phone=?, address=? WHERE id=?', 
                 [$name, $email, $phone, $address, $clientId]);
        $_SESSION['client_name'] = $name;
        $_SESSION['flash'] = 'Profile updated successfully!';
    }
    
    if ($action === 'add_to_cart') {
        $itemId = (int)$_POST['item_id'];
        $qty = (int)$_POST['quantity'];
        if ($qty > 0) {
            if (isset($_SESSION['cart'][$itemId])) {
                $_SESSION['cart'][$itemId] += $qty;
            } else {
                $_SESSION['cart'][$itemId] = $qty;
            }
            $_SESSION['flash'] = 'Item added to cart!';
        }
        $tab = $_GET['tab'] ?? '';
        $redirectPage = $tab ? "menu&tab=$tab" : 'menu';
    }
    
    if ($action === 'remove_cart') {
        $itemId = (int)$_POST['item_id'];
        unset($_SESSION['cart'][$itemId]);
        $_SESSION['flash'] = 'Item removed from cart!';
        $redirectPage = 'cart';
    }
    
    header("Location: clientdash.php?page=$redirectPage");
    exit;
}

// Flash messages
$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

// --- DATA FETCHING ---
$clientData = [];
if ($page === 'home' || $page === 'profile') {
    $clientData = dbFetchOne('SELECT * FROM clients WHERE id=?', [$clientId]);
}

$stats = ['active_orders' => 0, 'upcoming_events' => 0, 'total_spent' => 0];
if ($page === 'home') {
    $stats['active_orders'] = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE client_id=? AND status NOT IN ('delivered','cancelled')", [$clientId])['c'];
    $stats['upcoming_events'] = dbFetchOne("SELECT COUNT(*) as c FROM events e JOIN orders o ON o.event_id=e.id WHERE o.client_id=? AND e.date >= CURDATE()", [$clientId])['c'];
    $stats['total_spent'] = dbFetchOne("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE client_id=? AND status != 'cancelled'", [$clientId])['t'];
    $recentOrders = dbFetchAll("SELECT o.id, e.type, e.date, o.status as order_status, p.status as payment_status, o.total_amount FROM orders o JOIN events e ON e.id = o.event_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.client_id=? ORDER BY o.created_at DESC LIMIT 5", [$clientId]);
}

$myOrders = [];
if ($page === 'orders') {
    $myOrders = dbFetchAll("SELECT o.id, e.type, e.date, o.status as order_status, p.status as payment_status, o.total_amount, o.created_at FROM orders o JOIN events e ON e.id = o.event_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.client_id=? ORDER BY o.created_at DESC", [$clientId]);
}

$menuItems = [];
if ($page === 'menu') {
    if (empty($_SESSION['event'])) {
        header("Location: clientdash.php?page=event_setup");
        exit;
    }
}

$cartDetails = [];
$cartTotal = 0;
if ($page === 'cart' || $page === 'checkout') {
    if (!empty($_SESSION['cart'])) {
        $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $items = dbFetchAll("SELECT id, name, price FROM menu_items WHERE id IN ($ids)");
        foreach ($items as $item) {
            $qty = $_SESSION['cart'][$item['id']];
            $itemTotal = $item['price'] * $qty;
            $cartTotal += $itemTotal;
            $item['qty'] = $qty;
            $item['total'] = $itemTotal;
            $cartDetails[] = $item;
        }
    }
}
$cartCount = array_sum($_SESSION['cart']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Gourmet Catering</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d4af37;
            --primary-hover: #b5952f;
            --bg-main: #f8fafc;
            --sidebar-bg: #1a1a1a;
            --text-dark: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-dark); display: flex; min-height: 100vh; }
        
        /* Layout */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: var(--text-light); display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0;}
        .main-content { margin-left: 260px; flex-grow: 1; padding: 2rem 3rem; }
        
        /* Sidebar Components */
        .sidebar-header { padding: 2rem 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), #fcd34d); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo svg { width: 20px; height: 20px; fill: #1a1a1a; }
        .sidebar-title { font-size: 1.25rem; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; }
        .nav-menu { padding: 1.5rem 1rem; flex-grow: 1; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 1rem 1.25rem; color: var(--text-muted); text-decoration: none; border-radius: 12px; margin-bottom: 0.5rem; transition: all 0.3s ease; font-weight: 500; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--text-light); }
        .nav-item.active { background: var(--primary); color: #1a1a1a; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { display: flex; align-items: center; gap: 12px; color: var(--danger); text-decoration: none; font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; }
        .logout-btn:hover { background: rgba(239,68,68,0.1); }

        /* Header & Profile */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .page-title { font-size: 1.75rem; font-weight: 600; }
        .header-actions { display: flex; gap: 1rem; align-items: center; }
        
        .cart-btn {
            background: var(--card-bg); border: 1px solid var(--border-color); padding: 0.5rem 1rem; border-radius: 50px; text-decoration: none; color: var(--text-dark); display: flex; align-items: center; gap: 8px; font-weight: 600; transition: all 0.3s;
        }
        .cart-btn:hover { border-color: var(--primary); color: var(--primary-hover); }
        .cart-badge { background: var(--primary); color: #1a1a1a; border-radius: 50%; padding: 2px 8px; font-size: 0.8rem; }

        .user-profile { display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 0.5rem 1rem; border-radius: 50px; border: 1px solid var(--border-color); }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--text-dark); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .user-name { font-size: 0.9rem; font-weight: 600; }
        .user-role { font-size: 0.75rem; color: var(--text-muted); }

        /* General Card styles */
        .card { background: var(--card-bg); border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); border: 1px solid var(--border-color); margin-bottom: 1.5rem;}
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { display: flex; align-items: center; gap: 1rem; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; background: rgba(212,175,55,0.15); color: var(--primary-hover); display: flex; align-items: center; justify-content: center; }
        .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; display: block;}
        .stat-label { font-size: 0.85rem; font-weight: 500; color: var(--text-muted); }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed, .status-completed { background: #d1fae5; color: #059669; }
        .status-delivered { background: #dbeafe; color: #2563eb; }
        .status-cancelled, .status-failed { background: #fee2e2; color: #dc2626; }
        .status-refunded { background: #f3f4f6; color: #4b5563; }

        /* Forms */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(212,175,55,0.1); }
        
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: inherit; font-size: 0.95rem; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: #1a1a1a; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212,175,55,0.2); }
        .btn-danger { background: #fee2e2; color: var(--danger); }
        .btn-danger:hover { background: #fca5a5; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-dark); }
        .btn-outline:hover { background: var(--border-color); }

        /* Menu Grid */
        .menu-category { margin-bottom: 2rem; }
        .menu-category h2 { margin-bottom: 1rem; font-size: 1.4rem; color: var(--primary-hover); border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;}
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .menu-item-card { border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; background: var(--card-bg); transition: box-shadow 0.2s;}
        .menu-item-card:hover { box-shadow: 0 8px 24px -8px rgba(0,0,0,0.1); }
        .item-name { font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; }
        .item-desc { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; flex-grow: 1; }
        .item-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem; }
        .item-price { font-weight: 700; font-size: 1.2rem; color: var(--primary-hover); }
        
        .add-cart-form { display: flex; gap: 0.5rem; align-items: center; }
        .qty-input { width: 60px; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 6px; text-align: center;}
        
        /* Alerts */
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        
        /* Cart Summary */
        .cart-summary { display: flex; gap: 2rem; align-items: flex-start; }
        .cart-items { flex: 2; }
        .checkout-box { flex: 1; background: var(--card-bg); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); position: sticky; top: 2rem;}
        .checkout-total { display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; border-top: 2px solid var(--border-color); padding-top: 1rem; margin-top: 1rem; margin-bottom: 1.5rem; }

        .event-banner { background: rgba(212,175,55,0.1); border: 1px solid var(--primary); padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .event-banner-details h3 { color: var(--primary-hover); margin-bottom: 0.25rem; }
        .event-banner-details p { color: var(--text-dark); font-size: 0.9rem; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="7" stroke="currentColor" fill="none" stroke-width="2"/><path d="M12 9v3l2 2" stroke="currentColor" fill="none" stroke-width="2"/></svg>
            </div>
            <div class="sidebar-title">Client Portal</div>
        </div>
        
        <nav class="nav-menu">
            <a href="clientdash.php?page=home" class="nav-item <?= $page==='home'?'active':'' ?>">Dashboard</a>
            <a href="clientdash.php?page=orders" class="nav-item <?= $page==='orders'?'active':'' ?>">My Orders</a>
            <a href="clientdash.php?page=event_setup" class="nav-item <?= in_array($page, ['event_setup', 'menu'])?'active':'' ?>">Start New Order</a>
            <a href="clientdash.php?page=profile" class="nav-item <?= $page==='profile'?'active':'' ?>">Profile Settings</a>
        </nav>

        <div class="sidebar-footer">
            <form action="auth.php" method="POST" id="logoutForm">
                <input type="hidden" name="action" value="logout">
                <a href="#" onclick="document.getElementById('logoutForm').submit();" class="logout-btn">Logout</a>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="page-title">
                <?= $page==='home' ? 'Welcome Back!' : 
                   ($page==='orders' ? 'My Orders' : 
                   ($page==='event_setup' ? 'Setup Your Event' : 
                   ($page==='menu' ? 'Browse Menu' : 
                   ($page==='profile' ? 'Profile Settings' : 'Shopping Cart')))) ?>
            </h1>
            
            <div class="header-actions">
                <a href="clientdash.php?page=cart" class="cart-btn">
                    🛒 Cart <span class="cart-badge"><?= $cartCount ?></span>
                </a>
                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($clientName) ?></span>
                        <span class="user-role">Client</span>
                    </div>
                    <div class="avatar"><?= strtoupper(substr($clientName, 0, 1)) ?></div>
                </div>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>

        <!-- HOME DASHBOARD -->
        <?php if ($page === 'home'): ?>
            <div class="stats-grid">
                <div class="card stat-card">
                    <div class="stat-icon">O</div>
                    <div>
                        <span class="stat-value"><?= $stats['active_orders'] ?></span>
                        <span class="stat-label">Active Orders</span>
                    </div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon">E</div>
                    <div>
                        <span class="stat-value"><?= $stats['upcoming_events'] ?></span>
                        <span class="stat-label">Upcoming Events</span>
                    </div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon">$</div>
                    <div>
                        <span class="stat-value">$<?= number_format($stats['total_spent'], 2) ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-bottom: 1rem;">Recent Orders</h2>
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted">No recent orders found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Event Type</th><th>Date</th><th>Amount</th><th>Order Status</th><th>Payment Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td>#<?= $ro['id'] ?></td>
                                <td><?= htmlspecialchars($ro['type']) ?></td>
                                <td><?= htmlspecialchars($ro['date']) ?></td>
                                <td>$<?= number_format($ro['total_amount'], 2) ?></td>
                                <td><span class="status-badge status-<?= $ro['order_status'] ?>"><?= $ro['order_status'] ?></span></td>
                                <td><span class="status-badge status-<?= $ro['payment_status'] ?? 'pending' ?>"><?= $ro['payment_status'] ?? 'Pending' ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <!-- MY ORDERS -->
        <?php elseif ($page === 'orders'): ?>
            <div class="card">
                <?php if (empty($myOrders)): ?>
                    <p class="text-muted">You haven't placed any orders yet. <a href="clientdash.php?page=event_setup" style="color:var(--primary-hover);">Start an order</a></p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Placed On</th><th>Event Details</th><th>Amount</th><th>Order Status</th><th>Payment Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myOrders as $o): ?>
                            <tr>
                                <td>#<?= $o['id'] ?></td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($o['type']) ?></strong><br>
                                    <small style="color:var(--text-muted);"><?= htmlspecialchars($o['date']) ?></small>
                                </td>
                                <td>$<?= number_format($o['total_amount'], 2) ?></td>
                                <td><span class="status-badge status-<?= $o['order_status'] ?>"><?= $o['order_status'] ?></span></td>
                                <td><span class="status-badge status-<?= $o['payment_status'] ?? 'pending' ?>"><?= $o['payment_status'] ?? 'Pending' ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <!-- EVENT SETUP -->
        <?php elseif ($page === 'event_setup'): ?>
            <div class="card" style="max-width: 600px;">
                <?php if (!empty($_SESSION['event'])): ?>
                    <div class="alert alert-success">
                        You have already set up an event. <a href="clientdash.php?page=menu" style="font-weight:700; color:#065f46;">Continue browsing the menu.</a>
                    </div>
                    <form action="Client Dashboard/events.php" method="POST">
                        <input type="hidden" name="action" value="clear_event">
                        <button type="submit" class="btn btn-outline">Cancel Event & Start Over</button>
                    </form>
                <?php else: ?>
                    <h2 style="margin-bottom: 0.5rem;">Step 1: Event Details</h2>
                    <p style="color:var(--text-muted); margin-bottom: 1.5rem;">Please provide details for the event you are catering before browsing the menu.</p>
                    
                    <form action="Client Dashboard/events.php" method="POST">
                        <input type="hidden" name="action" value="setup_event">
                        
                        <div class="form-group">
                            <label>Event Type</label>
                            <input type="text" name="event_type" class="form-control" placeholder="e.g. Wedding Reception" required>
                        </div>
                        
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                            <div class="form-group">
                                <label>Event Date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Event Time</label>
                                <input type="time" name="event_time" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="event_location" class="form-control" placeholder="Full address of venue" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Estimated Guest Count</label>
                            <input type="number" name="guest_count" class="form-control" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Additional Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any dietary requirements or special instructions..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Save & View Menu</button>
                    </form>
                <?php endif; ?>
            </div>

        <!-- BROWSE MENU -->
        <?php elseif ($page === 'menu'): ?>
            <?php require_once 'Client Dashboard/menu.php'; ?>

        <!-- SHOPPING CART / PAYMENT -->
        <?php elseif ($page === 'cart'): ?>
            <?php require_once 'Client Dashboard/payment.php'; ?>

        <!-- PROFILE -->
        <?php elseif ($page === 'profile'): ?>
            <div class="card" style="max-width: 600px;">
                <h2 style="margin-bottom: 1.5rem;">Edit Profile</h2>
                <form action="clientdash.php?page=profile" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($clientData['full_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($clientData['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($clientData['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($clientData['address'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>
