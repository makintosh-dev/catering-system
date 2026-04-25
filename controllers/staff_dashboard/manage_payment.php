<?php
session_start();
require_once '../../auth.php';

// Ensure the user is logged in and has staff/admin role
requireRole('admin', 'manager', 'staff'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_payment_status') {
        $orderId = (int)$_POST['order_id'];
        $status = $_POST['payment_status'];
        dbExecute("UPDATE payments SET status = ? WHERE order_id = ?", [$status, $orderId]);
        $_SESSION['flash'] = "Payment for Order #$orderId updated to $status.";
    }

    header("Location: ../../admindash.php?page=orders");
    exit;
}

// Fallback
header("Location: ../../admindash.php");
exit;
