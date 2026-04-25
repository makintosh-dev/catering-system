<?php
session_start();
require_once '../auth.php';

// Ensure the user is logged in and has staff/admin role
requireRole('admin', 'manager', 'staff'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_menu') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name) {
            dbExecute("INSERT INTO menus (name, description) VALUES (?, ?)", [$name, $description]);
            $_SESSION['flash'] = "Menu '$name' created successfully.";
        } else {
            $_SESSION['flash'] = "Menu name is required.";
        }
    }
    
    if ($action === 'delete_menu') {
        $menuId = (int)$_POST['menu_id'];
        dbExecute("DELETE FROM menus WHERE id = ?", [$menuId]);
        $_SESSION['flash'] = "Menu deleted successfully.";
    }
    
    if ($action === 'add_item') {
        $menuId = (int)$_POST['menu_id'];
        $categoryId = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)$_POST['price'];
        
        if ($menuId && $categoryId && $name && $price >= 0) {
            dbExecute("INSERT INTO menu_items (menu_id, category_id, name, description, price) VALUES (?, ?, ?, ?, ?)", 
                      [$menuId, $categoryId, $name, $description, $price]);
            $_SESSION['flash'] = "Item '$name' added successfully.";
        } else {
            $_SESSION['flash'] = "Failed to add item. Please fill all required fields.";
        }
    }
    
    if ($action === 'delete_item') {
        $itemId = (int)$_POST['item_id'];
        dbExecute("DELETE FROM menu_items WHERE id = ?", [$itemId]);
        $_SESSION['flash'] = "Menu item deleted successfully.";
    }

    header("Location: ../admindash.php?page=menus");
    exit;
}

header("Location: ../admindash.php?page=menus");
exit;
