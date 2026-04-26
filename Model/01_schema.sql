CREATE DATABASE IF NOT EXISTS catering_db 
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


-- 1. USERS  (staff / admin accounts)

CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_users PRIMARY KEY (id),
    CONSTRAINT uq_users_uname UNIQUE (username),
    CONSTRAINT uq_users_email UNIQUE (email)
);


-- 2. USER_PROFILES

CREATE TABLE user_profiles(
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    gender VARCHAR(10),

    CONSTRAINT pk_user_profiles PRIMARY KEY (id),
    CONSTRAINT uq_user_profiles_uid UNIQUE (user_id),
    CONSTRAINT fk_user_profiles_users FOREIGN KEY (user_id) REFERENCES users (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- 3. CLIENTS

CREATE TABLE clients(
    id INT NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_clients PRIMARY KEY (id),
    CONSTRAINT uq_clients_email UNIQUE (email)
);


-- 4. EVENTS

CREATE TABLE events(
    id INT NOT NULL AUTO_INCREMENT,
    type VARCHAR(80) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location TEXT NOT NULL,
    guest_count INT NOT NULL CHECK (guest_count > 0),

    CONSTRAINT pk_events PRIMARY KEY (id)
);


-- 5. ORDERS

CREATE TABLE IF NOT EXISTS orders (
    id INT NOT NULL AUTO_INCREMENT,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    status ENUM('pending','confirmed','in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (total_amount >= 0),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT pk_orders PRIMARY KEY (id),
    CONSTRAINT fk_orders_client FOREIGN KEY (client_id) REFERENCES clients (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_event FOREIGN KEY (event_id) REFERENCES events (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 6. CATEGORIES

CREATE TABLE categories(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    description TEXT,

    CONSTRAINT pk_categories PRIMARY KEY (id),
    CONSTRAINT uq_categories_name UNIQUE (name)
);


-- 7. MENUS

CREATE TABLE menus(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,

    CONSTRAINT pk_menus PRIMARY KEY (id)
);


-- 8. MENU_ITEMS

CREATE TABLE menu_items(
    id INT NOT NULL AUTO_INCREMENT,
    menu_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL CHECK (price >= 0),
    is_available TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT pk_menu_items PRIMARY KEY (id),
    CONSTRAINT fk_menu_items_menu FOREIGN KEY (menu_id) REFERENCES menus (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_menu_items_cat FOREIGN KEY (category_id) REFERENCES categories (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 9. ORDER_ITEMS

CREATE TABLE order_items (
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1 CHECK (quantity > 0),

    CONSTRAINT pk_order_items PRIMARY KEY (order_id, menu_item_id),
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_oi_menu_item FOREIGN KEY (menu_item_id) REFERENCES menu_items (id) 
    ON DELETE RESTRICT ON UPDATE CASCADE
);


-- 10. PAYMENTS

CREATE TABLE payments (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_date DATE NOT NULL,
    method ENUM('cash','card','bank_transfer','online') NOT NULL,
    status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    reference_no VARCHAR(100),
    
    CONSTRAINT pk_payments PRIMARY KEY (id),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) 
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
