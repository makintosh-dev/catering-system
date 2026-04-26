<?php
session_start();
require_once '../../auth.php';
requireClientLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Setup Event Details in Session
    if ($action === 'setup_event') {
        $_SESSION['event'] = [
            'type'        => trim($_POST['event_type']),
            'date'        => trim($_POST['event_date']),
            'time'        => trim($_POST['event_time']),
            'location'    => trim($_POST['event_location']),
            'guest_count' => (int)$_POST['guest_count'],
            'notes'       => trim($_POST['notes'] ?? '')
        ];
        header("Location: ../../clientdash.php?page=menu");
        exit;
    }

    // 2. Clear Event Details from Session
    if ($action === 'clear_event') {
        unset($_SESSION['event']);
        $_SESSION['cart'] = [];
        header("Location: ../../clientdash.php?page=event_setup");
        exit;
    }

    // 3. Add entire package to cart
    if ($action === 'add_package_to_cart') {
        $menuId     = (int)$_POST['menu_id'];
        $guestCount = $_SESSION['event']['guest_count'] ?? 1;

        // Fetch all items for this menu
        $items = dbFetchAll('SELECT id FROM menu_items WHERE menu_id=? AND is_available=1', [$menuId]);
        foreach ($items as $item) {
            $_SESSION['cart'][$item['id']] = $guestCount;
        }

        $_SESSION['flash'] = 'Package added to cart!';
        header("Location: ../../clientdash.php?page=cart");
        exit;
    }

    // 4. Checkout and Book the Event/Order
    if ($action === 'checkout') {
        $paymentMethod = $_POST['payment_method'] ?? '';

        if (empty($_SESSION['cart']) || empty($_SESSION['event']) || empty($paymentMethod)) {
            $_SESSION['flash_error'] = 'Invalid cart, missing event details, or missing payment method.';
            header("Location: ../../clientdash.php?page=cart");
            exit;
        }

        $clientId = $_SESSION['client_id'];
        $e        = $_SESSION['event'];

        // Calculate total
        $total     = 0;
        $cartItems = [];
        foreach ($_SESSION['cart'] as $itemId => $qty) {
            $item = dbFetchOne('SELECT price FROM menu_items WHERE id=?', [$itemId]);
            if ($item) {
                $total              += $item['price'] * $qty;
                $cartItems[$itemId]  = ['qty' => $qty, 'price' => $item['price']];
            }
        }

        // Create Event
        dbExecute('INSERT INTO events (type, date, time, location, guest_count) VALUES (?, ?, ?, ?, ?)',
                  [$e['type'], $e['date'], $e['time'], $e['location'], $e['guest_count']]);
        $eventId = dbLastId();

        // Create Order (Defaulting user_id to 1)
        $staffId = 1;
        dbExecute('INSERT INTO orders (client_id, user_id, event_id, status, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?)',
                  [$clientId, $staffId, $eventId, 'pending', $total, $e['notes']]);
        $orderId = dbLastId();

        // Create Order Items
        foreach ($cartItems as $itemId => $data) {
            dbExecute('INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)',
                      [$orderId, $itemId, $data['qty']]);
        }

        // Create Payment Record
        $paymentDate = date('Y-m-d');
        dbExecute("INSERT INTO payments (order_id, amount, payment_date, method, status) VALUES (?, ?, ?, ?, 'pending')",
                  [$orderId, $total, $paymentDate, $paymentMethod]);

        // Clear cart and event
        $_SESSION['cart'] = [];
        unset($_SESSION['event']);

        $_SESSION['flash'] = 'Order placed successfully! We will contact you soon to confirm.';
        header("Location: ../../clientdash.php?page=orders");
        exit;
    }
}

// Fallback redirect
header("Location: ../../clientdash.php");
exit;
