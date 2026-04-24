-- =============================================================
--  CATERING SERVICE SYSTEM — Sample Data (DML)
--  File: 02_sample_data.sql
--  Passwords are bcrypt hashes of "Password123"
-- =============================================================

USE catering_db;

-- -----------------------------------------------------------
-- USERS
-- -----------------------------------------------------------
INSERT INTO users (username, password_hash, full_name, email, role) VALUES
('admin',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmed Khan',      'admin@caterco.pk',    'admin'),
('manager01',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sara Malik',      'sara@caterco.pk',     'manager'),
('staff01',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bilal Hussain',   'bilal@caterco.pk',    'staff'),
('staff02',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zara Qureshi',    'zara@caterco.pk',     'staff'),
('manager02',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usman Tariq',     'usman@caterco.pk',    'manager');

-- -----------------------------------------------------------
-- USER_PROFILES
-- -----------------------------------------------------------
INSERT INTO user_profiles (user_id, phone, address, gender) VALUES
(1, '+92-300-1111111', 'House 1, F-7, Islamabad',  'Male'),
(2, '+92-311-2222222', 'Street 5, Gulberg, Lahore', 'Female'),
(3, '+92-321-3333333', 'Block B, DHA, Karachi',     'Male'),
(4, '+92-333-4444444', 'Saddar, Peshawar',           'Female'),
(5, '+92-345-5555555', 'G-10, Islamabad',            'Male');

-- -----------------------------------------------------------
-- CLIENTS
-- -----------------------------------------------------------
INSERT INTO clients (full_name, password_hash, email, phone, address) VALUES
('Fatima Nawaz',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fatima@gmail.com',   '+92-300-9991111', 'Model Town, Lahore'),
('Ali Raza',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ali.raza@email.com', '+92-321-8882222', 'Hayatabad, Peshawar'),
('Nadia Bashir',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nadia.b@mail.com',   '+92-311-7773333', 'Clifton, Karachi'),
('Tariq Iqbal',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tariq@web.com',      '+92-333-6664444', 'F-8, Islamabad'),
('Hina Shahid',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hina.s@email.com',   '+92-345-5555555', 'Saddar, Peshawar'),
('Omar Farooq',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'omar.f@mail.com',    '+92-300-4446666', 'Gulshan, Karachi');

-- -----------------------------------------------------------
-- EVENTS
-- -----------------------------------------------------------
INSERT INTO events (type, date, time, location, guest_count) VALUES
('Wedding Reception',    '2025-02-14', '18:00:00', 'Pearl Continental, Lahore',     350),
('Corporate Dinner',     '2025-03-05', '19:30:00', 'Serena Hotel, Islamabad',        80),
('Birthday Party',       '2025-03-22', '15:00:00', 'Private Villa, Karachi',          60),
('Engagement Ceremony',  '2025-04-10', '17:00:00', 'Avari Hotel, Lahore',            200),
('Family Gathering',     '2025-04-25', '13:00:00', 'Home Lawn, Peshawar',             90),
('Product Launch',       '2025-05-12', '10:00:00', 'Convention Center, Islamabad',   150);

-- -----------------------------------------------------------
-- CATEGORIES
-- -----------------------------------------------------------
INSERT INTO categories (name, description) VALUES
('Starters',      'Appetizers and soups served before the main course'),
('Main Course',   'Primary hot dishes — rice, meat, and curries'),
('BBQ',           'Grilled and smoked items'),
('Desserts',      'Sweet dishes and mithai'),
('Beverages',     'Hot and cold drinks'),
('Breads',        'Naan, roti, paratha and other bread varieties');

-- -----------------------------------------------------------
-- MENUS
-- -----------------------------------------------------------
INSERT INTO menus (name, description) VALUES
('Wedding Deluxe Package',   'Full-service wedding menu with 25+ dishes'),
('Corporate Standard',       'Professional business event menu'),
('BBQ Night Special',        'Charcoal BBQ and grill menu'),
('Birthday Fiesta',          'Fun party menu with desserts and snacks'),
('Family Dawat Package',     'Traditional home-style dawat menu');

-- -----------------------------------------------------------
-- MENU_ITEMS
-- -----------------------------------------------------------
INSERT INTO menu_items (menu_id, category_id, name, description, price, is_available) VALUES
-- Wedding Deluxe (menu 1)
(1, 1, 'Shami Kebab',        'Pan-fried minced meat patties',           250.00, 1),
(1, 1, 'Chicken Corn Soup',  'Creamy chicken soup with sweet corn',     180.00, 1),
(1, 2, 'Chicken Karahi',     'Tomato-based wok chicken',                650.00, 1),
(1, 2, 'Mutton Biryani',     'Aromatic slow-cooked mutton rice',        750.00, 1),
(1, 2, 'Beef Nihari',        'Slow-cooked spiced beef stew',            700.00, 1),
(1, 4, 'Gulab Jamun',        'Deep-fried milk solids in sugar syrup',   150.00, 1),
(1, 4, 'Kheer',              'Rice pudding with cardamom',              130.00, 1),
(1, 6, 'Garlic Naan',        'Tandoor-baked garlic flatbread',           60.00, 1),
-- Corporate Standard (menu 2)
(2, 1, 'Bruschetta',         'Toasted bread with tomato topping',       200.00, 1),
(2, 2, 'Grilled Chicken',    'Herb-marinated grilled chicken breast',   800.00, 1),
(2, 2, 'Vegetable Pulao',    'Fragrant rice with mixed vegetables',     300.00, 1),
(2, 5, 'Mineral Water',      '500ml chilled mineral water',              50.00, 1),
(2, 5, 'Soft Drinks',        'Assorted canned sodas',                    80.00, 1),
-- BBQ Night (menu 3)
(3, 3, 'Seekh Kebab',        'Spiced minced meat skewers',              350.00, 1),
(3, 3, 'Chicken Tikka',      'Marinated bone-in chicken pieces',        400.00, 1),
(3, 3, 'Mutton Chops',       'Marinated grilled lamb chops',            600.00, 1),
(3, 3, 'Fish Tikka',         'Spiced grilled fish fillets',             450.00, 1),
(3, 5, 'Fresh Lemonade',     'Chilled lemon drink with mint',           120.00, 1),
-- Birthday Fiesta (menu 4)
(4, 1, 'Mini Samosa',        'Crispy pastry with spiced filling',       100.00, 1),
(4, 4, 'Chocolate Cake',     'Three-layer chocolate fudge cake',        500.00, 1),
(4, 4, 'Ice Cream Sundae',   'Vanilla ice cream with toppings',         200.00, 1),
(4, 5, 'Mocktail Punch',     'Fruit punch with sparkling water',        150.00, 1),
-- Family Dawat (menu 5)
(5, 2, 'Daal Makhani',       'Slow-cooked black lentils in butter',     280.00, 1),
(5, 2, 'Chicken Qorma',      'Yogurt and nut-based chicken curry',      600.00, 1),
(5, 6, 'Tandoori Roti',      'Whole wheat tandoor bread',                40.00, 1),
(5, 4, 'Ras Malai',          'Cottage cheese patties in cream',         160.00, 1);

-- -----------------------------------------------------------
-- ORDERS
-- -----------------------------------------------------------
INSERT INTO orders (client_id, user_id, event_id, status, total_amount, notes) VALUES
(1, 2, 1, 'confirmed',    245000.00, 'Bride requested extra Gulab Jamun portions'),
(2, 3, 2, 'delivered',     64000.00, 'Vegetarian option needed for 10 guests'),
(3, 4, 3, 'confirmed',     38000.00, 'Outdoor BBQ setup required'),
(4, 2, 4, 'pending',      130000.00, 'Flower decoration with catering'),
(5, 5, 5, 'in_progress',   47000.00, NULL),
(6, 3, 6, 'confirmed',     92000.00, 'Branding banners needed at buffet stations');

-- -----------------------------------------------------------
-- ORDER_ITEMS
-- -----------------------------------------------------------
INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES
-- Order 1 (Wedding)
(1,  1, 200), (1,  2, 200), (1,  3, 150), (1,  4, 150),
(1,  5, 100), (1,  6, 350), (1,  7, 350), (1,  8, 400),
-- Order 2 (Corporate)
(2,  9,  80), (2, 10,  60), (2, 11,  80), (2, 12,  80), (2, 13,  80),
-- Order 3 (BBQ)
(3, 14,  60), (3, 15,  60), (3, 16,  40), (3, 17,  30), (3, 18,  60),
-- Order 4 (Engagement)
(4,  1, 150), (4,  3, 120), (4,  4, 120), (4,  6, 200), (4,  8, 250),
-- Order 5 (Family)
(5, 23,  90), (5, 24,  90), (5, 25, 100), (5, 26,  90),
-- Order 6 (Product Launch)
(6,  9, 120), (6, 10, 100), (6, 11, 150), (6, 12, 150), (6, 13, 150);

-- -----------------------------------------------------------
-- PAYMENTS
-- -----------------------------------------------------------
INSERT INTO payments (order_id, amount, payment_date, method, status, reference_no) VALUES
(1, 100000.00, '2025-01-20', 'bank_transfer', 'completed', 'BT-20250120-001'),
(1, 145000.00, '2025-02-15', 'bank_transfer', 'completed', 'BT-20250215-002'),
(2,  64000.00, '2025-03-05', 'card',          'completed', 'CARD-20250305-003'),
(3,  20000.00, '2025-03-01', 'cash',          'completed', NULL),
(3,  18000.00, '2025-03-22', 'cash',          'completed', NULL),
(4,  50000.00, '2025-04-01', 'online',        'completed', 'ONL-20250401-006'),
(5,  47000.00, '2025-04-20', 'bank_transfer', 'pending',   'BT-20250420-007'),
(6,  92000.00, '2025-05-10', 'card',          'completed', 'CARD-20250510-008');

-- -----------------------------------------------------------
-- DEMO: UPDATE & DELETE
-- -----------------------------------------------------------

-- Update an order status
UPDATE orders SET status = 'confirmed' WHERE id = 5;

-- Mark a menu item unavailable
UPDATE menu_items SET is_available = 0 WHERE id = 12;

-- Soft-cancel demonstration (then restore)
-- UPDATE orders SET status = 'cancelled' WHERE id = 4;

-- Delete a payment record (refund scenario)
-- DELETE FROM payments WHERE id = 7;
