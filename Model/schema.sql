CREATE DATABASE IF NOT EXISTS catering_db 
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


-- 1. USERS  (staff / admin accounts)

CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- 2. USER_PROFILES

CREATE TABLE user_profiles(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    gender VARCHAR(10),

    FOREIGN KEY (user_id) REFERENCES users (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
);


-- 3. CLIENTS

CREATE TABLE clients(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- 4. EVENTS

CREATE TABLE events(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(80) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location TEXT NOT NULL,
    guest_count INT NOT NULL CHECK (guest_count > 0)
);


-- 5. ORDERS

CREATE TABLE IF NOT EXISTS orders (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    status ENUM('pending','confirmed','in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (total_amount >= 0),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES clients (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 6. CATEGORIES

CREATE TABLE categories(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description TEXT
);


-- 7. MENUS

CREATE TABLE menus(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);


-- 8. MENU_ITEMS

CREATE TABLE menu_items(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL CHECK (price >= 0),
    is_available TINYINT(1) NOT NULL DEFAULT 1,

    FOREIGN KEY (menu_id) REFERENCES menus (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 9. ORDER_ITEMS

CREATE TABLE order_items (
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1 CHECK (quantity > 0),

    CONSTRAINT pk_order_items PRIMARY KEY (order_id, menu_item_id),
    FOREIGN KEY (order_id) REFERENCES orders (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 10. PAYMENTS

CREATE TABLE payments (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_date DATE NOT NULL,
    method ENUM('cash','card','bank_transfer','online') NOT NULL,
    status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    reference_no VARCHAR(100),
    
    FOREIGN KEY (order_id) REFERENCES orders (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- INDEXES


-- users

CREATE INDEX idx_users_role ON users(role);

-- user_profiles 


-- clients 

CREATE INDEX idx_clients_created ON clients(created_at);


-- events

CREATE INDEX idx_events_date ON events(date);
CREATE INDEX idx_events_type ON events(type);


-- orders

CREATE INDEX idx_orders_client ON orders(client_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_event ON orders(event_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);
CREATE INDEX idx_orders_status_date ON orders(status, created_at);

-- categories

-- UNIQUE on name already creates a defauly (implicit) index.


-- menus

CREATE INDEX idx_menus_name ON menus(name);


-- menu_items

CREATE INDEX idx_menu_items_menu ON menu_items(menu_id);
CREATE INDEX idx_menu_items_cat ON menu_items(category_id);
CREATE INDEX idx_menu_items_avail ON menu_items(is_available);
CREATE INDEX idx_menu_items_name ON menu_items(name);
CREATE INDEX idx_menu_items_avail_cat ON menu_items(is_available, category_id);


-- order_items

CREATE INDEX idx_oi_menu_item ON order_items(menu_item_id);


-- payments

CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_payments_method_stat ON payments(method, status);





-- VIEWS

-- Full Order Summary

CREATE OR REPLACE VIEW vw_order AS
SELECT o.id AS order_id,
    c.id AS client_id,
    c.full_name AS client_name,
    c.email AS client_email,
    c.phone AS client_phone,
    e.id AS event_id,
    e.type AS event_type,
    e.date AS event_date,
    e.time AS event_time,
    e.location AS event_location,
    e.guest_count,
    u.id AS staff_id,
    u.full_name AS managed_by,
    u.role AS staff_role,
    o.status, o.total_amount, o.notes, o.created_at FROM orders o

JOIN clients c ON c.id = o.client_id
JOIN events e ON e.id = o.event_id
JOIN users u ON u.id = o.user_id;


-- Order Payment Tracker

CREATE OR REPLACE VIEW vw_payments AS
SELECT o.id AS order_id,
    c.full_name AS client_name,
    o.status AS order_status,
    o.total_amount,
    COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount END), 0) AS total_paid,
    o.total_amount - COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount END), 0) AS balance_due,
    COUNT(p.id) AS payment_count,
    GROUP_CONCAT(DISTINCT p.method ORDER BY p.payment_date SEPARATOR ', ') AS payment_methods
FROM orders o JOIN clients c ON c.id = o.client_id
LEFT JOIN payments p ON p.order_id = o.id
GROUP BY o.id, c.full_name, o.status, o.total_amount;


-- Menu Catalogue

CREATE OR REPLACE VIEW vw_menu_catalogue AS
SELECT mi.id AS item_id,
    m.id AS menu_id,
    m.name AS menu_name,
    cat.id AS category_id,
    cat.name AS category,
    mi.name AS item_name,
    mi.description, mi.price, mi.is_available
FROM menu_items mi JOIN menus m ON m.id = mi.menu_id
JOIN categories cat ON cat.id = mi.category_id;


-- Order Item Review

CREATE OR REPLACE VIEW vw_order_item AS
SELECT oi.order_id,
    mi.id AS item_id,
    mi.name AS item_name,
    cat.name AS category,
    m.name AS menu_name,
    oi.quantity,
    mi.price AS unit_price,
    (oi.quantity * mi.price) AS line_total
FROM order_items oi
JOIN menu_items mi  ON mi.id  = oi.menu_item_id
JOIN categories cat ON cat.id = mi.category_id
JOIN menus m ON m.id = mi.menu_id;


-- Top-Selling Items

CREATE OR REPLACE VIEW vw_top_items AS
SELECT mi.id AS item_id,
    mi.name AS item_name,
    m.name AS menu_name,
    cat.name AS category,
    mi.price,
    SUM(oi.quantity) AS total_qty_ordered,
    SUM(oi.quantity * mi.price)  AS total_revenue
FROM order_items oi
JOIN menu_items mi ON mi.id = oi.menu_item_id
JOIN menus m ON m.id = mi.menu_id
JOIN categories cat ON cat.id = mi.category_id
GROUP BY mi.id, mi.name, m.name, cat.name, mi.price
ORDER BY total_qty_ordered DESC;


-- Staff Workload

CREATE OR REPLACE VIEW vw_staff_workload AS
SELECT u.id, u.username, u.full_name, u.role,
    COUNT(o.id) AS total_orders,
    SUM(o.total_amount) AS total_revenue_managed
FROM users u LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id, u.username, u.full_name, u.role;


-- Client Overview

CREATE OR REPLACE VIEW vw_client AS
SELECT c.id, c.full_name, c.email, c.phone, c.address,
    c.created_at AS registered_on,
    COUNT(o.id) AS total_orders,
    COALESCE(SUM(o.total_amount), 0) AS lifetime_spend,
    MAX(o.created_at) AS last_order_date
FROM clients c LEFT JOIN orders o ON o.client_id = c.id
GROUP BY c.id, c.full_name, c.email, c.phone, c.address, c.created_at;


-- Monthly Revenue Report

CREATE OR REPLACE VIEW vw_monthly_revenue AS
SELECT YEAR(o.created_at) AS year,
    MONTH(o.created_at) AS month,
    COUNT(*) AS total_orders,
    SUM(o.total_amount) AS total_revenue,
    AVG(o.total_amount) AS avg_order_value
FROM orders o GROUP BY YEAR(o.created_at), MONTH(o.created_at)
ORDER BY year, month;


-- Revenue by Payment Method

CREATE OR REPLACE VIEW vw_revenue AS
SELECT p.method,
    COUNT(*) AS transactions,
    SUM(p.amount) AS total_collected,
    MIN(p.amount) AS smallest_payment,
    MAX(p.amount) AS largest_payment,
    AVG(p.amount) AS avg_payment
FROM payments p WHERE p.status = 'completed' GROUP BY p.method;


-- Upcoming event

CREATE OR REPLACE VIEW vw_upcoming_events AS
SELECT e.id AS event_id,
    e.type AS event_type,
    e.date AS event_date,
    e.time AS event_time,
    e.location, e.guest_count,
    o.id AS order_id,
    o.status AS order_status,
    o.total_amount,
    c.id AS client_id,
    c.full_name AS client_name,
    c.phone AS client_phone
FROM events e JOIN orders  o ON o.event_id = e.id
JOIN clients c ON c.id = o.client_id WHERE e.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY e.date, e.time;
