<?php
session_start();
require_once '../auth.php';

// Ensure the user is logged in and has staff/admin role
requireRole('admin', 'manager', 'staff'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_client') {
        $clientId = (int)$_POST['client_id'];
        
        try {
            // Attempt to delete. This will throw an exception if the client has existing orders due to ON DELETE RESTRICT constraint.
            dbExecute("DELETE FROM clients WHERE id = ?", [$clientId]);
            $_SESSION['flash'] = "Client removed successfully.";
        } catch (mysqli_sql_exception $e) {
            // Error code 1451 is "Cannot delete or update a parent row: a foreign key constraint fails"
            if ($e->getCode() === 1451) {
                $_SESSION['flash_error'] = "Cannot remove client: This client has existing orders in the system. To maintain auditing integrity, clients with an order history cannot be deleted.";
            } else {
                $_SESSION['flash_error'] = "An error occurred while trying to remove the client.";
            }
        }
    }
    
    if ($action === 'force_delete_client') {
        $clientId = (int)$_POST['client_id'];
        
        try {
            // 1. Get all orders for this client
            $orders = dbFetchAll("SELECT id, event_id FROM orders WHERE client_id = ?", [$clientId]);
            
            if (!empty($orders)) {
                $orderIds = array_column($orders, 'id');
                $eventIds = array_column($orders, 'event_id');
                
                $orderIdsStr = implode(',', $orderIds);
                $eventIdsStr = implode(',', $eventIds);
                
                // 2. Delete payments for these orders
                dbExecute("DELETE FROM payments WHERE order_id IN ($orderIdsStr)");
                
                // 3. Delete order_items for these orders
                dbExecute("DELETE FROM order_items WHERE order_id IN ($orderIdsStr)");
                
                // 4. Delete the orders
                dbExecute("DELETE FROM orders WHERE client_id = ?", [$clientId]);
                
                // 5. Delete the events
                dbExecute("DELETE FROM events WHERE id IN ($eventIdsStr)");
            }
            
            // 6. Delete the client
            dbExecute("DELETE FROM clients WHERE id = ?", [$clientId]);
            $_SESSION['flash'] = "Client and all associated data forcefully removed successfully.";
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "An error occurred during force removal: " . $e->getMessage();
        }
    }

    header("Location: ../admindash.php?page=clients");
    exit;
}

header("Location: ../admindash.php?page=clients");
exit;
