# 🍽️ Mashaal Catering System

A web-based catering management platform built with PHP and MySQL, following an MVC architecture. It supports two user roles — **Clients** and **Staff/Admin** — each with their own dedicated portal.

---

## 📋 Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Database Setup](#database-setup)
- [How to Run](#how-to-run)
- [Project Structure](#project-structure)
- [User Roles](#user-roles)
- [Default Credentials](#default-credentials)

---

## ✨ Features

### Client Portal
- Register and log in as a client
- Set up event details (type, date, time, location, guest count)
- Browse pre-set catering packages or build a custom menu
- Add items to cart with per-guest quantity scaling
- Checkout and place orders with a chosen payment method
- View order history and payment statuses
- Edit profile (name, email, phone, address)

### Staff / Admin Portal
- Log in as staff or admin
- View live dashboard with order and revenue stats
- Manage and update order statuses (Pending → Confirmed → In Progress → Finished)
- Manage payment statuses (Pending → Completed → Failed → Refunded)
- Manage menu packages and individual menu items
- View client directory and remove clients
- View reports and analytics (revenue trends, top items, order distribution)

---

## ⚙️ Prerequisites

| Requirement | Version | Notes |
|---|---|---|
| **PHP** | 8.0 or higher | Must have PDO and PDO_MySQL extensions enabled |
| **MySQL** | 5.7 or higher (or MariaDB 10.4+) | Used as the database backend |
| **Web Server** | Apache / Nginx **or** PHP built-in server | Apache recommended for production |
| **Composer** | Optional | Not required; no external PHP dependencies |
| **Browser** | Any modern browser | Chrome, Firefox, Edge recommended |

### Enabling PHP Extensions (if needed)

Open your `php.ini` file and make sure these lines are **uncommented** (remove the leading `;`):

```ini
extension=pdo_mysql
extension=mysqli
```

---

## 🗄️ Database Setup

1. Open **MySQL Workbench**, **phpMyAdmin**, or your preferred MySQL client.

2. Create a new database:
   ```sql
   CREATE DATABASE mashaal_catering CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Import the provided SQL schema file (if included) **or** run the following table structure manually:

   ```sql
   USE mashaal_catering;

   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(100) NOT NULL UNIQUE,
       password_hash VARCHAR(255) NOT NULL,
       full_name VARCHAR(150) NOT NULL,
       email VARCHAR(150) NOT NULL UNIQUE,
       role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE clients (
       id INT AUTO_INCREMENT PRIMARY KEY,
       full_name VARCHAR(150) NOT NULL,
       email VARCHAR(150) NOT NULL UNIQUE,
       password_hash VARCHAR(255) NOT NULL,
       phone VARCHAR(20),
       address TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE categories (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(100) NOT NULL
   );

   CREATE TABLE menus (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(150) NOT NULL,
       description TEXT
   );

   CREATE TABLE menu_items (
       id INT AUTO_INCREMENT PRIMARY KEY,
       menu_id INT,
       category_id INT NOT NULL,
       name VARCHAR(150) NOT NULL,
       description TEXT,
       price DECIMAL(10,2) NOT NULL,
       is_available TINYINT(1) DEFAULT 1,
       FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE SET NULL,
       FOREIGN KEY (category_id) REFERENCES categories(id)
   );

   CREATE TABLE events (
       id INT AUTO_INCREMENT PRIMARY KEY,
       type VARCHAR(100) NOT NULL,
       date DATE NOT NULL,
       time TIME,
       location VARCHAR(255),
       guest_count INT DEFAULT 1
   );

   CREATE TABLE orders (
       id INT AUTO_INCREMENT PRIMARY KEY,
       client_id INT NOT NULL,
       user_id INT NOT NULL,
       event_id INT NOT NULL,
       status ENUM('pending','confirmed','in_progress','delivered','cancelled') DEFAULT 'pending',
       total_amount DECIMAL(10,2) NOT NULL,
       notes TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (client_id) REFERENCES clients(id),
       FOREIGN KEY (user_id) REFERENCES users(id),
       FOREIGN KEY (event_id) REFERENCES events(id)
   );

   CREATE TABLE order_items (
       id INT AUTO_INCREMENT PRIMARY KEY,
       order_id INT NOT NULL,
       menu_item_id INT NOT NULL,
       quantity INT NOT NULL,
       FOREIGN KEY (order_id) REFERENCES orders(id),
       FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
   );

   CREATE TABLE payments (
       id INT AUTO_INCREMENT PRIMARY KEY,
       order_id INT NOT NULL UNIQUE,
       amount DECIMAL(10,2) NOT NULL,
       payment_date DATE,
       method VARCHAR(50),
       status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
       FOREIGN KEY (order_id) REFERENCES orders(id)
   );
   ```

4. Configure your database connection in **`db.php`** at the project root:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'mashaal_catering');
   define('DB_USER', 'root');       // your MySQL username
   define('DB_PASS', '');           // your MySQL password
   ```

---

## ▶️ How to Run

### Option A — PHP Built-in Development Server (Quickest)

1. Open a terminal and navigate to the project root:
   ```bash
   cd "e:\1. SE\Projects\Catering System"
   ```

2. Start the PHP server:
   ```bash
   php -S localhost:8000
   ```

3. Open your browser and visit:
   - **Client Login:** [http://localhost:8000/login_client.php](http://localhost:8000/login_client.php)
   - **Staff Login:** [http://localhost:8000/login_staff.php](http://localhost:8000/login_staff.php)
   - **Sign Up:** [http://localhost:8000/signup.php](http://localhost:8000/signup.php)

### Option B — Apache (XAMPP / WAMP / Laragon)

1. Copy the entire project folder into your web server's document root:
   - **XAMPP:** `C:\xampp\htdocs\catering-system\`
   - **WAMP:** `C:\wamp64\www\catering-system\`
   - **Laragon:** `C:\laragon\www\catering-system\`

2. Start Apache and MySQL from your control panel.

3. Open your browser and visit:
   - `http://localhost/catering-system/login_client.php`
   - `http://localhost/catering-system/login_staff.php`

---

## 📁 Project Structure

```
Catering System/
│
├── admindash.php              # Staff/Admin front controller (MVC entry point)
├── clientdash.php             # Client front controller (MVC entry point)
├── auth.php                   # Authentication functions (login, logout, guards)
├── db.php                     # Database connection and helper functions
├── login_staff.php            # Staff login page
├── login_client.php           # Client login/register page
├── signup.php                 # Client registration page
│
├── controllers/
│   ├── admindashlogic.php     # Admin dashboard logic & data fetching
│   ├── clientdashlogic.php    # Client dashboard logic & data fetching
│   ├── staff_dashboard/       # Staff action handlers
│   │   ├── status.php         # Update order status
│   │   ├── manage_payment.php # Update payment status
│   │   ├── client_actions.php # Delete/force-delete clients
│   │   └── menu_actions.php   # Add/delete menu items
│   └── client_dashboard/      # Client action handlers
│       └── events.php         # Event setup, checkout, cart actions
│
├── views/
│   ├── admindashUI.php        # Admin dashboard HTML/CSS template
│   ├── clientdashUI.php       # Client dashboard HTML/CSS template
│   ├── staff_dashboard/       # Staff sub-page views
│   │   ├── manage_menu.php    # Menus & packages management UI
│   │   └── reports.php        # Reports & analytics UI
│   └── client_dashboard/      # Client sub-page views
│       ├── menu.php           # Browse menu UI
│       └── payment.php        # Cart & checkout UI
│
├── Model/                     # Database model layer
├── assets/                    # Static assets (CSS, images, JS)
└── project report/            # Project documentation
    └── Mashaal_Catering_System_Report.docx
```

---

## 👥 User Roles

| Role | Access | Entry Point |
|---|---|---|
| **Client** | Client portal — browse menu, place orders, view history | `login_client.php` |
| **Staff** | Staff portal — manage orders, payments, clients | `login_staff.php` |
| **Manager** | Same as Staff | `login_staff.php` |
| **Admin** | Full access — same as Staff + all privileges | `login_staff.php` |

---

## 🔑 Default Credentials

> **Important:** Create a staff account by inserting directly into the `users` table (password must be bcrypt hashed). Clients can self-register via `signup.php`.

To create an admin account manually:

```sql
INSERT INTO users (username, password_hash, full_name, email, role)
VALUES (
    'admin',
    '$2y$12$HASH_GENERATED_BY_PHP',   -- use password_hash('yourpassword', PASSWORD_DEFAULT)
    'Administrator',
    'admin@mashaalcatering.com',
    'admin'
);
```

Or generate a hash using PHP:
```bash
php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.x |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, Vanilla CSS, JavaScript |
| **Fonts** | Google Fonts — Outfit |
| **Charts** | Chart.js (CDN) |
| **Architecture** | MVC (Model-View-Controller) |
| **Currency** | Pakistani Rupee (PKR) |

---

## 📞 Support

For issues or questions regarding this system, contact the development team or refer to the full project report in the `project report/` folder.
