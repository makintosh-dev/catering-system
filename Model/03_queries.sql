-- =============================================================
--  CATERING SERVICE SYSTEM — Queries, Views & Indexes
--  File: 03_queries.sql
-- =============================================================

USE catering_db;

-- ===========================================================
--  SECTION A — INDEXES
-- ===========================================================

CREATE INDEX idx_orders_client      ON orders      (client_id);
CREATE INDEX idx_orders_status      ON orders      (status);
CREATE INDEX idx_orders_created     ON orders      (created_at);
CREATE INDEX idx_menu_items_menu    ON menu_items  (menu_id);
CREATE INDEX idx_menu_items_cat     ON menu_items  (category_id);
CREATE INDEX idx_payments_order     ON payments    (order_id);
CREATE INDEX idx_payments_status    ON payments    (status);
CREATE INDEX idx_order_items_item   ON order_items (menu_item_id);
CREATE INDEX idx_clients_email      ON clients     (email);
CREATE INDEX idx_events_date        ON events      (date);

-- ===========================================================
--  SECTION B — VIEWS
-- ===========================================================

-- B1. Full order summary (order + client + event + staff)
CREATE OR REPLACE VIEW vw_order_summary AS
SELECT
    o.id            AS order_id,
    c.full_name     AS client_name,
    c.email         AS client_email,
    c.phone         AS client_phone,
    e.type          AS event_type,
    e.date          AS event_date,
    e.location      AS event_location,
    e.guest_count,
    u.full_name     AS managed_by,
    o.status,
    o.total_amount,
    o.notes,
    o.created_at
FROM orders o
JOIN clients c ON c.id = o.client_id
JOIN events  e ON e.id = o.event_id
JOIN users   u ON u.id = o.user_id;

-- B2. Revenue per order (sum of payments)
CREATE OR REPLACE VIEW vw_order_payments AS
SELECT
    o.id          AS order_id,
    o.total_amount,
    SUM(p.amount) AS total_paid,
    (o.total_amount - COALESCE(SUM(p.amount),0)) AS balance_due,
    GROUP_CONCAT(p.method ORDER BY p.payment_date SEPARATOR ', ') AS payment_methods
FROM orders o
LEFT JOIN payments p ON p.order_id = o.id AND p.status = 'completed'
GROUP BY o.id, o.total_amount;

-- B3. Menu item catalogue with category & menu name
CREATE OR REPLACE VIEW vw_menu_catalogue AS
SELECT
    mi.id           AS item_id,
    m.name          AS menu_name,
    cat.name        AS category,
    mi.name         AS item_name,
    mi.description,
    mi.price,
    IF(mi.is_available, 'Yes', 'No') AS available
FROM menu_items mi
JOIN menus      m   ON m.id   = mi.menu_id
JOIN categories cat ON cat.id = mi.category_id;

-- B4. Top-selling items (by total quantity ordered)
CREATE OR REPLACE VIEW vw_top_items AS
SELECT
    mi.id,
    mi.name         AS item_name,
    m.name          AS menu_name,
    cat.name        AS category,
    mi.price,
    SUM(oi.quantity)               AS total_qty_ordered,
    SUM(oi.quantity * mi.price)    AS total_revenue
FROM order_items oi
JOIN menu_items mi  ON mi.id  = oi.menu_item_id
JOIN menus      m   ON m.id   = mi.menu_id
JOIN categories cat ON cat.id = mi.category_id
GROUP BY mi.id, mi.name, m.name, cat.name, mi.price
ORDER BY total_qty_ordered DESC;

-- B5. Staff workload — orders managed per user
CREATE OR REPLACE VIEW vw_staff_workload AS
SELECT
    u.id,
    u.full_name,
    u.role,
    COUNT(o.id)           AS total_orders,
    SUM(o.total_amount)   AS total_revenue_managed
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id, u.full_name, u.role;

-- ===========================================================
--  SECTION C — BASIC SELECT QUERIES
-- ===========================================================

-- C1. All active (available) menu items
SELECT * FROM menu_items WHERE is_available = 1;

-- C2. Orders placed this month
SELECT * FROM orders
WHERE MONTH(created_at) = MONTH(CURDATE())
  AND YEAR(created_at)  = YEAR(CURDATE());

-- C3. Clients registered in 2025
SELECT id, full_name, email, phone
FROM clients
WHERE YEAR(created_at) = 2025
ORDER BY created_at DESC;

-- C4. Payments still pending
SELECT p.*, o.total_amount, c.full_name AS client
FROM payments p
JOIN orders  o ON o.id = p.order_id
JOIN clients c ON c.id = o.client_id
WHERE p.status = 'pending';

-- ===========================================================
--  SECTION D — JOIN QUERIES
-- ===========================================================

-- D1. Order details with client, event and managing staff
SELECT
    o.id            AS order_id,
    c.full_name     AS client,
    e.type          AS event,
    e.date          AS event_date,
    u.full_name     AS staff,
    o.status,
    o.total_amount
FROM orders  o
JOIN clients c ON c.id = o.client_id
JOIN events  e ON e.id = o.event_id
JOIN users   u ON u.id = o.user_id
ORDER BY e.date;

-- D2. Full order item breakdown (order → item → menu → category)
SELECT
    oi.order_id,
    mi.name        AS item_name,
    cat.name       AS category,
    m.name         AS menu,
    oi.quantity,
    mi.price,
    (oi.quantity * mi.price) AS line_total
FROM order_items oi
JOIN menu_items mi  ON mi.id  = oi.menu_item_id
JOIN categories cat ON cat.id = mi.category_id
JOIN menus      m   ON m.id   = mi.menu_id
ORDER BY oi.order_id, cat.name;

-- D3. Clients who have at least one order (INNER JOIN)
SELECT DISTINCT c.id, c.full_name, c.email, c.phone
FROM clients c
JOIN orders o ON o.client_id = c.id;

-- D4. All clients with total spend (LEFT JOIN)
SELECT
    c.id,
    c.full_name,
    c.email,
    COUNT(o.id)          AS total_orders,
    COALESCE(SUM(o.total_amount), 0) AS lifetime_spend
FROM clients c
LEFT JOIN orders o ON o.client_id = c.id
GROUP BY c.id, c.full_name, c.email
ORDER BY lifetime_spend DESC;

-- D5. User profile details (1-to-1 JOIN)
SELECT
    u.id,
    u.username,
    u.full_name,
    u.email,
    u.role,
    up.phone,
    up.address,
    up.gender
FROM users u
JOIN user_profiles up ON up.user_id = u.id;

-- ===========================================================
--  SECTION E — SUBQUERIES
-- ===========================================================

-- E1. Clients who have never placed an order
SELECT id, full_name, email
FROM clients
WHERE id NOT IN (SELECT DISTINCT client_id FROM orders);

-- E2. Orders where total_amount is above the average
SELECT * FROM orders
WHERE total_amount > (SELECT AVG(total_amount) FROM orders);

-- E3. Most expensive menu item per category
SELECT *
FROM menu_items mi
WHERE price = (
    SELECT MAX(price)
    FROM menu_items
    WHERE category_id = mi.category_id
);

-- E4. Clients whose lifetime spend exceeds 100,000
SELECT id, full_name, email
FROM clients
WHERE id IN (
    SELECT client_id
    FROM orders
    GROUP BY client_id
    HAVING SUM(total_amount) > 100000
);

-- E5. Events that have a confirmed or delivered order
SELECT *
FROM events
WHERE id IN (
    SELECT event_id
    FROM orders
    WHERE status IN ('confirmed', 'delivered')
);

-- ===========================================================
--  SECTION F — AGGREGATE / REPORT QUERIES
-- ===========================================================

-- F1. Total revenue by order status
SELECT
    status,
    COUNT(*)          AS order_count,
    SUM(total_amount) AS total_revenue,
    AVG(total_amount) AS avg_order_value,
    MIN(total_amount) AS min_order,
    MAX(total_amount) AS max_order
FROM orders
GROUP BY status;

-- F2. Monthly revenue report
SELECT
    YEAR(created_at)  AS year,
    MONTH(created_at) AS month,
    COUNT(*)          AS orders,
    SUM(total_amount) AS revenue
FROM orders
GROUP BY YEAR(created_at), MONTH(created_at)
ORDER BY year, month;

-- F3. Revenue collected per payment method
SELECT
    method,
    COUNT(*)    AS transactions,
    SUM(amount) AS total_collected
FROM payments
WHERE status = 'completed'
GROUP BY method
ORDER BY total_collected DESC;

-- F4. Events by type with order count and total value
SELECT
    e.type,
    COUNT(o.id)          AS total_orders,
    SUM(o.total_amount)  AS total_revenue,
    AVG(e.guest_count)   AS avg_guests
FROM events e
LEFT JOIN orders o ON o.event_id = e.id
GROUP BY e.type
ORDER BY total_revenue DESC;

-- F5. Guest count distribution
SELECT
    CASE
        WHEN guest_count <= 50            THEN 'Small (≤50)'
        WHEN guest_count BETWEEN 51 AND 150 THEN 'Medium (51-150)'
        WHEN guest_count BETWEEN 151 AND 300 THEN 'Large (151-300)'
        ELSE 'Grand (300+)'
    END           AS event_size,
    COUNT(*)      AS event_count
FROM events
GROUP BY event_size;

-- ===========================================================
--  SECTION G — SEARCH & FILTER QUERIES
--  (Parameterised versions — replace ? with actual values)
-- ===========================================================

-- G1. Search orders by client name
SELECT vos.*
FROM vw_order_summary vos
WHERE vos.client_name LIKE CONCAT('%', 'Ali', '%');

-- G2. Filter orders by status and date range
SELECT *
FROM orders
WHERE status      = 'confirmed'
  AND created_at BETWEEN '2025-01-01' AND '2025-12-31';

-- G3. Search menu items by name or description
SELECT * FROM menu_items
WHERE (name LIKE '%chicken%' OR description LIKE '%chicken%')
  AND is_available = 1;

-- G4. Filter payments by method and status
SELECT p.*, c.full_name AS client
FROM payments p
JOIN orders  o ON o.id = p.order_id
JOIN clients c ON c.id = o.client_id
WHERE p.method = 'card'
  AND p.status = 'completed';

-- ===========================================================
--  SECTION H — DASHBOARD KPI QUERIES
-- ===========================================================

-- H1. Overall dashboard counters
SELECT
    (SELECT COUNT(*) FROM clients)                          AS total_clients,
    (SELECT COUNT(*) FROM orders)                           AS total_orders,
    (SELECT COUNT(*) FROM orders WHERE status='pending')    AS pending_orders,
    (SELECT COUNT(*) FROM orders WHERE status='confirmed')  AS confirmed_orders,
    (SELECT COUNT(*) FROM orders WHERE status='delivered')  AS delivered_orders,
    (SELECT COUNT(*) FROM menu_items WHERE is_available=1)  AS active_menu_items,
    (SELECT SUM(amount) FROM payments WHERE status='completed') AS total_revenue_collected;

-- H2. Recent 5 orders for dashboard feed
SELECT
    o.id,
    c.full_name  AS client,
    e.type       AS event,
    o.status,
    o.total_amount,
    o.created_at
FROM orders  o
JOIN clients c ON c.id = o.client_id
JOIN events  e ON e.id = o.event_id
ORDER BY o.created_at DESC
LIMIT 5;

-- H3. Upcoming events in the next 30 days
SELECT
    e.*,
    o.status AS order_status,
    c.full_name AS client
FROM events  e
JOIN orders  o ON o.event_id = e.id
JOIN clients c ON c.id = o.client_id
WHERE e.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY e.date;
