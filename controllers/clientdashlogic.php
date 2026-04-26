<?php
// This logic file is required by clientdash.php at the root level,
// so all relative paths (like 'auth.php') resolve correctly.

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
        $name    = trim($_POST['full_name']);
        $email   = trim($_POST['email']);
        $phone   = trim($_POST['phone']);
        $address = trim($_POST['address']);

        dbExecute('UPDATE clients SET full_name=?, email=?, phone=?, address=? WHERE id=?',
                  [$name, $email, $phone, $address, $clientId]);
        $_SESSION['client_name'] = $name;
        $_SESSION['flash'] = 'Profile updated successfully!';
    }

    if ($action === 'add_to_cart') {
        $itemId      = (int)$_POST['item_id'];
        $qtyPerGuest = (int)$_POST['quantity'];

        $guestCount = $_SESSION['event']['guest_count'] ?? 1;
        $totalQty   = $qtyPerGuest * $guestCount;

        if ($totalQty > 0) {
            if (isset($_SESSION['cart'][$itemId])) {
                $_SESSION['cart'][$itemId] += $totalQty;
            } else {
                $_SESSION['cart'][$itemId] = $totalQty;
            }
            $_SESSION['flash'] = 'Item added for all guests!';
        }
        $tab          = $_GET['tab'] ?? '';
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
$flash      = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

// --- DATA FETCHING (powered by SQL views) ---
$clientData = [];
if ($page === 'home' || $page === 'profile') {
    $clientData = dbFetchOne('SELECT * FROM clients WHERE id=?', [$clientId]);
}

$stats = ['active_orders' => 0, 'upcoming_events' => 0, 'total_spent' => 0];
$recentOrders = [];
if ($page === 'home') {
    $stats['active_orders']   = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE client_id=? AND status NOT IN ('delivered','cancelled')", [$clientId])['c'];
    // vw_upcoming_events already filters for next 30 days
    $stats['upcoming_events'] = dbFetchOne("SELECT COUNT(*) as c FROM vw_upcoming_events WHERE client_name = (SELECT full_name FROM clients WHERE id=?)", [$clientId])['c'];
    $stats['total_spent']     = dbFetchOne("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE client_id=? AND status != 'cancelled'", [$clientId])['t'];

    // vw_order replaces orders→events JOIN; add payment status via LEFT JOIN
    $recentOrders = dbFetchAll("
        SELECT vs.order_id AS id, vs.event_type AS type, vs.event_date AS date,
               vs.status AS order_status, p.status AS payment_status, vs.total_amount
        FROM vw_order vs
        LEFT JOIN payments p ON p.order_id = vs.order_id
        WHERE vs.client_id = ?
        ORDER BY vs.created_at DESC LIMIT 5
    ", [$clientId]);
}

$myOrders = [];
if ($page === 'orders') {
    // vw_order replaces the 2-table JOIN for order listing
    $myOrders = dbFetchAll("
        SELECT vs.order_id AS id, vs.event_type AS type, vs.event_date AS date,
               vs.status AS order_status, p.status AS payment_status,
               vs.total_amount, vs.created_at
        FROM vw_order vs
        LEFT JOIN payments p ON p.order_id = vs.order_id
        WHERE vs.client_id = ?
        ORDER BY vs.created_at DESC
    ", [$clientId]);
}

if ($page === 'menu') {
    if (empty($_SESSION['event'])) {
        header("Location: clientdash.php?page=event_setup");
        exit;
    }
}

$cartDetails = [];
$cartTotal   = 0;
if ($page === 'cart' || $page === 'checkout') {
    if (!empty($_SESSION['cart'])) {
        $ids   = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $items = dbFetchAll("SELECT id, name, price FROM menu_items WHERE id IN ($ids)");
        foreach ($items as $item) {
            $qty       = $_SESSION['cart'][$item['id']];
            $itemTotal = $item['price'] * $qty;
            $cartTotal += $itemTotal;
            $item['qty']   = $qty;
            $item['total'] = $itemTotal;
            $cartDetails[] = $item;
        }
    }
}
$cartCount = array_sum($_SESSION['cart']);
?>
