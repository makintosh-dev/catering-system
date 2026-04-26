<?php
// This logic file is required by admindash.php at the root level, 
// so all relative paths (like 'auth.php') resolve correctly.

session_start();
require_once 'auth.php';

// Ensure the user is logged in and has staff/admin role
requireRole('admin', 'manager', 'staff'); 
$user = currentUser();
$page = $_GET['page'] ?? 'home';

// POST handlers moved to Staff Dashboard/status.php and Staff Dashboard/manage_payment.php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// --- DATA FETCHING (powered by SQL views) ---
$stats = [];
if ($page === 'home') {
    $stats['pending_orders']     = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")['c'];
    $stats['in_progress_orders'] = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'in_progress'")['c'];
    $stats['delivered_orders']   = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'delivered'")['c'];
    $stats['upcoming_events']    = dbFetchOne("SELECT COUNT(*) as c FROM vw_upcoming_events")['c'];
    $stats['active_clients']     = dbFetchOne("SELECT COUNT(*) as c FROM clients")['c'];
    $stats['total_revenue']      = dbFetchOne("SELECT COALESCE(SUM(total_amount), 0) as t FROM orders WHERE status != 'cancelled'")['t'];

    // Recent Activity — vw_order already flattens orders→clients→events→staff
    $recentActivity = dbFetchAll("
        SELECT vs.order_id AS id, vs.client_name AS full_name, vs.event_type,
               vs.total_amount, vs.status, p.status AS payment_status, vs.created_at
        FROM vw_order vs
        LEFT JOIN payments p ON p.order_id = vs.order_id
        ORDER BY vs.created_at DESC LIMIT 5
    ");
}

$ordersList = [];
if ($page === 'orders') {
    // vw_order replaces the 3-table JOIN; just add payment status
    $ordersList = dbFetchAll("
        SELECT vs.order_id AS id, vs.client_name AS full_name, vs.event_type,
               vs.event_date, vs.total_amount, vs.status AS order_status,
               p.status AS payment_status, vs.created_at
        FROM vw_order vs
        LEFT JOIN payments p ON p.order_id = vs.order_id
        ORDER BY vs.created_at DESC
    ");
}

$clientsList = [];
if ($page === 'clients') {
    // vw_client adds order count and lifetime spend to each client row
    $clientsList = dbFetchAll("SELECT * FROM vw_client ORDER BY registered_on DESC");
}

$menusList = [];
$categoriesList = [];
if ($page === 'menus') {
    $categoriesList = dbFetchAll("SELECT * FROM categories ORDER BY name ASC");
    $packages = dbFetchAll("SELECT * FROM menus");
    foreach ($packages as $pkg) {
        // vw_menu replaces the menu_items→categories JOIN
        $items = dbFetchAll(
            "SELECT item_id AS id, item_name AS name, price, category FROM vw_menu WHERE menu_id = ?",
            [$pkg['id']]
        );
        $menusList[] = ['package' => $pkg, 'items' => $items];
    }
}

$reportData = [];
if ($page === 'reports') {
    // vw_monthly_revenue replaces the GROUP BY on orders
    $reportData['revenue'] = dbFetchAll("
        SELECT DATE_FORMAT(CONCAT(year,'-',LPAD(month,2,'0'),'-01'), '%b %Y') AS month,
               total_revenue AS total
        FROM vw_monthly_revenue
        ORDER BY year, month
        LIMIT 12
    ");
    $reportData['status_distribution'] = dbFetchAll("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    // vw_top_items replaces the 4-table aggregate query
    $reportData['top_items'] = dbFetchAll("SELECT * FROM vw_top_items LIMIT 5");
}
?>
