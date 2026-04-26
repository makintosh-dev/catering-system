<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Mashaal Catering System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #d4af37;
            --primary-hover: #b5952f;
            --bg-main: #f8fafc;
            --sidebar-bg: #1e1e24;
            --text-dark: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: var(--text-light); display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0;}
        .sidebar-header { padding: 2rem 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-logo { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), #fcd34d); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo svg { width: 20px; height: 20px; fill: #1a1a1a; }
        .sidebar-title { font-size: 1.25rem; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; }
        .nav-menu { padding: 1.5rem 1rem; flex-grow: 1; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 1rem 1.25rem; color: var(--text-muted); text-decoration: none; border-radius: 12px; margin-bottom: 0.5rem; transition: all 0.3s ease; font-weight: 500; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.05); color: var(--text-light); }
        .nav-item.active { background: var(--primary); color: #1a1a1a; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .logout-btn { display: flex; align-items: center; gap: 12px; color: #ef4444; text-decoration: none; font-weight: 500; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s ease; }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1); }

        /* Main Content */
        .main-content { margin-left: 260px; flex-grow: 1; padding: 2rem 3rem; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .page-title { font-size: 1.75rem; font-weight: 600; color: var(--text-dark); }
        .user-profile { display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 0.5rem 1rem; border-radius: 50px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border-color); }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #1a1a1a; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize; }

        /* General Card styles */
        .card { background: var(--card-bg); border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); border: 1px solid var(--border-color); margin-bottom: 1.5rem;}
        
        /* Dashboard Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: var(--card-bg); padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1rem; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; background: rgba(212, 175, 55, 0.15); color: var(--primary-hover); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem; }
        .stat-details { display: flex; flex-direction: column; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--text-dark); line-height: 1.2; }
        .stat-label { font-size: 0.85rem; font-weight: 500; color: var(--text-muted); }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; display: inline-block;}
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed, .status-completed { background: #d1fae5; color: #059669; }
        .status-delivered { background: #dbeafe; color: #2563eb; }
        .status-cancelled, .status-failed { background: #fee2e2; color: #dc2626; }
        .status-refunded { background: #f3f4f6; color: #4b5563; }

        /* Inline Forms */
        .inline-form { display: flex; align-items: center; gap: 0.5rem; }
        .inline-select { padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem; background: var(--bg-main); }
        .btn-update { padding: 0.4rem 0.8rem; background: var(--text-dark); color: white; border: none; border-radius: 6px; font-size: 0.8rem; cursor: pointer; }
        .btn-update:hover { background: #000; }

        /* Alerts */
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        
        .empty-state { text-align: center; padding: 3rem 0; color: var(--text-muted); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="7" stroke="currentColor" fill="none" stroke-width="2"/><path d="M12 9v3l2 2" stroke="currentColor" fill="none" stroke-width="2"/></svg>
            </div>
            <div class="sidebar-title">Staff Portal</div>
        </div>
        
        <nav class="nav-menu">
            <a href="admindash.php?page=home" class="nav-item <?= $page==='home'?'active':'' ?>">Dashboard</a>
            <a href="admindash.php?page=orders" class="nav-item <?= $page==='orders'?'active':'' ?>">Manage Orders</a>
            <a href="admindash.php?page=clients" class="nav-item <?= $page==='clients'?'active':'' ?>">Clients</a>
            <a href="admindash.php?page=menus" class="nav-item <?= $page==='menus'?'active':'' ?>">Menus & Packages</a>
            <a href="admindash.php?page=reports" class="nav-item <?= $page==='reports'?'active':'' ?>">Reports & Analytics</a>
        </nav>

        <div class="sidebar-footer">
            <form action="auth.php" method="POST" id="logoutForm">
                <input type="hidden" name="action" value="logout">
                <a href="#" onclick="document.getElementById('logoutForm').submit();" class="logout-btn">Logout</a>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="page-title">
                <?= $page==='home' ? 'Overview' : 
                   ($page==='orders' ? 'Manage Orders' : 
                   ($page==='clients' ? 'Client Directory' : 
                   ($page==='reports' ? 'Reports & Analytics' : 'Menus & Packages'))) ?>
            </h1>
            
            <div class="user-profile">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'Staff Member') ?></span>
                    <span class="user-role"><?= htmlspecialchars($user['role'] ?? 'Staff') ?> Account</span>
                </div>
                <div class="avatar"><?= strtoupper(substr($user['full_name'] ?? 'S', 0, 1)) ?></div>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500;"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>

        <!-- DASHBOARD HOME -->
        <?php if ($page === 'home'): ?>
            <div class="stats-grid">
                <a href="admindash.php?page=orders" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">!</div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $stats['pending_orders'] ?></span>
                        <span class="stat-label">Pending Orders</span>
                    </div>
                </a>
                <a href="admindash.php?page=orders" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">⚙️</div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $stats['in_progress_orders'] ?></span>
                        <span class="stat-label">In Progress</span>
                    </div>
                </a>
                <a href="admindash.php?page=orders" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">✓</div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $stats['delivered_orders'] ?></span>
                        <span class="stat-label">Finished Orders</span>
                    </div>
                </a>
                <a href="admindash.php?page=reports" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon" style="background: rgba(212, 175, 55, 0.1); color: #d4af37;">$</div>
                    <div class="stat-details">
                        <span class="stat-value">PKR <?= number_format($stats['total_revenue'], 2) ?></span>
                        <span class="stat-label">Total Revenue</span>
                    </div>
                </a>
                <a href="admindash.php?page=orders" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $stats['upcoming_events'] ?></span>
                        <span class="stat-label">Upcoming Events</span>
                    </div>
                </a>
                <a href="admindash.php?page=clients" class="stat-card" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $stats['active_clients'] ?></span>
                        <span class="stat-label">Active Clients</span>
                    </div>
                </a>
            </div>

            <div class="card">
                <h2>Recent Activity</h2>
                <?php if (empty($recentActivity)): ?>
                    <div class="empty-state">No recent activity to display.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr><th>Order ID</th><th>Client</th><th>Event Type</th><th>Amount</th><th>Order Status</th><th>Payment Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $o): ?>
                            <tr>
                                <td>#<?= $o['id'] ?></td>
                                <td><?= htmlspecialchars($o['full_name']) ?></td>
                                <td><?= htmlspecialchars($o['event_type']) ?></td>
                                <td>PKR <?= number_format($o['total_amount'], 2) ?></td>
                                <td><span class="status-badge status-<?= $o['status'] ?>"><?= $o['status'] === 'delivered' ? 'Finished' : ucfirst(str_replace('_', ' ', $o['status'])) ?></span></td>
                                <td><span class="status-badge status-<?= $o['payment_status'] ?? 'pending' ?>"><?= $o['payment_status'] ?? 'Pending' ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <!-- MANAGE ORDERS -->
        <?php elseif ($page === 'orders'): ?>
            <div class="card">
                <?php if (empty($ordersList)): ?>
                    <div class="empty-state">No orders found in the system.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Client</th>
                                <th>Event Date</th>
                                <th>Amount</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordersList as $o): ?>
                            <tr>
                                <td>#<?= $o['id'] ?></td>
                                <td><?= htmlspecialchars($o['full_name']) ?></td>
                                <td><?= htmlspecialchars($o['event_date']) ?></td>
                                <td>PKR <?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <form action="Staff Dashboard/status.php" method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="status" class="inline-select">
                                            <option value="pending" <?= $o['order_status']==='pending'?'selected':'' ?>>Pending</option>
                                            <option value="confirmed" <?= $o['order_status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                            <option value="in_progress" <?= $o['order_status']==='in_progress'?'selected':'' ?>>In Progress</option>
                                            <option value="delivered" <?= $o['order_status']==='delivered'?'selected':'' ?>>Finished</option>
                                            <option value="cancelled" <?= $o['order_status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn-update">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($o['payment_status'] !== null): ?>
                                    <form action="Staff Dashboard/manage_payment.php" method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="update_payment_status">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="payment_status" class="inline-select">
                                            <option value="pending" <?= $o['payment_status']==='pending'?'selected':'' ?>>Pending</option>
                                            <option value="completed" <?= $o['payment_status']==='completed'?'selected':'' ?>>Completed</option>
                                            <option value="failed" <?= $o['payment_status']==='failed'?'selected':'' ?>>Failed</option>
                                            <option value="refunded" <?= $o['payment_status']==='refunded'?'selected':'' ?>>Refunded</option>
                                        </select>
                                        <button type="submit" class="btn-update">Save</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:0.85rem;">No Payment Record</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <!-- CLIENTS -->
        <?php elseif ($page === 'clients'): ?>
            <div class="card">
                <?php if (empty($clientsList)): ?>
                    <div class="empty-state">No clients found.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientsList as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                <td>
                                    <form action="Staff Dashboard/client_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this client? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_client">
                                        <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.85rem; font-weight: 600; padding: 0.4rem 0.6rem; border-radius: 6px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='none'">Remove</button>
                                    </form>
                                    <form action="Staff Dashboard/client_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('WARNING: Force removing a client will PERMANENTLY delete their entire order history, payments, and events. Are you absolutely sure?');">
                                        <input type="hidden" name="action" value="force_delete_client">
                                        <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #7f1d1d; cursor: pointer; font-size: 0.85rem; font-weight: 600; padding: 0.4rem 0.6rem; border-radius: 6px;" onmouseover="this.style.background='rgba(127, 29, 29, 0.1)'" onmouseout="this.style.background='none'">Force Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <!-- MENUS & PACKAGES -->
        <?php elseif ($page === 'menus'): ?>
            <?php require_once 'views/staff_dashboard/manage_menu.php'; ?>
        <!-- REPORTS -->
        <?php elseif ($page === 'reports'): ?>
            <?php require_once 'views/staff_dashboard/reports.php'; ?>
        <?php endif; ?>

    </main>
</body>
</html>
