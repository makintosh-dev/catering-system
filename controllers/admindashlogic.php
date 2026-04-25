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

// --- DATA FETCHING ---
$stats = [];
if ($page === 'home') {
    $stats['pending_orders'] = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")['c'];
    $stats['in_progress_orders'] = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'in_progress'")['c'];
    $stats['delivered_orders'] = dbFetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'delivered'")['c'];
    $stats['upcoming_events'] = dbFetchOne("SELECT COUNT(*) as c FROM events WHERE date >= CURDATE()")['c'];
    $stats['active_clients'] = dbFetchOne("SELECT COUNT(*) as c FROM clients")['c'];
    $stats['total_revenue'] = dbFetchOne("SELECT COALESCE(SUM(total_amount), 0) as t FROM orders WHERE status != 'cancelled'")['t'];
    
    $recentActivity = dbFetchAll("
        SELECT o.id, c.full_name, e.type as event_type, o.total_amount, o.status, p.status as payment_status, o.created_at 
        FROM orders o 
        JOIN clients c ON c.id = o.client_id 
        JOIN events e ON e.id = o.event_id 
        LEFT JOIN payments p ON p.order_id = o.id
        ORDER BY o.created_at DESC LIMIT 5
    ");
}

$ordersList = [];
if ($page === 'orders') {
    $ordersList = dbFetchAll("
        SELECT o.id, c.full_name, e.type as event_type, e.date as event_date, o.total_amount, o.status as order_status, p.status as payment_status, o.created_at 
        FROM orders o 
        JOIN clients c ON c.id = o.client_id 
        JOIN events e ON e.id = o.event_id 
        LEFT JOIN payments p ON p.order_id = o.id
        ORDER BY o.created_at DESC
    ");
}

$clientsList = [];
if ($page === 'clients') {
    $clientsList = dbFetchAll("SELECT * FROM clients ORDER BY created_at DESC");
}

$menusList = [];
$categoriesList = [];
if ($page === 'menus') {
    $categoriesList = dbFetchAll("SELECT * FROM categories ORDER BY name ASC");
    $packages = dbFetchAll("SELECT * FROM menus");
    foreach($packages as $pkg) {
        $items = dbFetchAll("SELECT mi.id, mi.name, mi.price, c.name as category FROM menu_items mi JOIN categories c ON c.id = mi.category_id WHERE mi.menu_id = ?", [$pkg['id']]);
        $menusList[] = ['package' => $pkg, 'items' => $items];
    }
}

$reportData = [];
if ($page === 'reports') {
    $reportData['revenue'] = dbFetchAll("SELECT DATE_FORMAT(created_at, '%b %Y') as month, SUM(total_amount) as total FROM orders WHERE status != 'cancelled' GROUP BY month ORDER BY MIN(created_at) ASC LIMIT 12");
    $reportData['status_distribution'] = dbFetchAll("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $reportData['top_items'] = dbFetchAll("
        SELECT 
            mi.name AS item_name, 
            m.name AS menu_name, 
            cat.name AS category, 
            SUM(oi.quantity) AS total_qty_ordered, 
            SUM(oi.quantity * mi.price) AS total_revenue 
        FROM order_items oi 
        JOIN menu_items mi ON mi.id = oi.menu_item_id 
        LEFT JOIN menus m ON m.id = mi.menu_id 
        JOIN categories cat ON cat.id = mi.category_id 
        GROUP BY mi.id, mi.name, m.name, cat.name 
        ORDER BY total_qty_ordered DESC 
        LIMIT 5
    ");
}
?>
