<?php
// Ensure this file is included from within the client dashboard
if (!isset($page) || $page !== 'menu') {
    exit;
}

// Fetch Pre-set Packages (Menus)
$packages = dbFetchAll("SELECT * FROM menus");

// Fetch Custom Menu Items
$customItems = [];
$items = dbFetchAll("SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON c.id = mi.category_id WHERE mi.is_available = 1 ORDER BY c.name, mi.name");
foreach ($items as $item) {
    $customItems[$item['category_name']][] = $item;
}

// Determine active tab
$tab = $_GET['tab'] ?? 'packages';
?>

<style>
.tab-link {
    padding: 1rem 2rem;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    display: inline-block;
}
.tab-link:hover {
    color: var(--text-dark);
}
.tab-link.active {
    color: var(--primary-hover);
    border-bottom: 3px solid var(--primary);
}
.tabs-container {
    display: flex;
    gap: 1rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 2rem;
}
</style>

<div class="event-banner">
    <div class="event-banner-details">
        <h3>Ordering for: <?= htmlspecialchars($_SESSION['event']['type']) ?></h3>
        <p><?= htmlspecialchars($_SESSION['event']['date']) ?> at <?= htmlspecialchars($_SESSION['event']['time']) ?> • <?= htmlspecialchars($_SESSION['event']['guest_count']) ?> Guests</p>
    </div>
    <div>
        <form action="controllers/client_dashboard/events.php" method="POST" style="display:inline;">
            <input type="hidden" name="action" value="clear_event">
            <button type="submit" class="btn btn-outline" style="font-size:0.85rem; padding: 0.5rem 1rem;">Cancel Order</button>
        </form>
    </div>
</div>

<div class="tabs-container">
    <a href="clientdash.php?page=menu&tab=packages" class="tab-link <?= $tab==='packages' ? 'active' : '' ?>">Pre-set Packages</a>
    <a href="clientdash.php?page=menu&tab=custom" class="tab-link <?= $tab==='custom' ? 'active' : '' ?>">Build Custom Menu</a>
</div>

<?php if ($tab === 'packages'): ?>
    <div class="menu-grid">
        <?php foreach ($packages as $pkg): ?>
            <div class="menu-item-card">
                <div>
                    <div class="item-name" style="font-size: 1.3rem; color: var(--primary-hover);"><?= htmlspecialchars($pkg['name']) ?></div>
                    <div class="item-desc"><?= htmlspecialchars($pkg['description']) ?></div>
                </div>
                <div class="item-footer" style="margin-top: 1.5rem; justify-content: center;">
                    <form action="controllers/client_dashboard/events.php" method="POST" style="width:100%;">
                        <input type="hidden" name="action" value="add_package_to_cart">
                        <input type="hidden" name="menu_id" value="<?= $pkg['id'] ?>">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Select Package</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php foreach ($customItems as $category => $catItems): ?>
        <div class="menu-category">
            <h2><?= htmlspecialchars($category) ?></h2>
            <div class="menu-grid">
                <?php foreach ($catItems as $item): ?>
                    <div class="menu-item-card">
                        <div>
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-desc"><?= htmlspecialchars($item['description']) ?></div>
                        </div>
                        <div class="item-footer">
                            <div class="item-price">PKR <?= number_format($item['price'], 2) ?></div>
                            <form action="clientdash.php?page=menu&tab=custom" method="POST" class="add-cart-form">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" class="qty-input" value="1" min="1" max="1000" title="Portions per guest" style="width: 60px; padding: 0.3rem;">
                                <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size:0.85rem;">Add</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div style="margin-top: 3rem; text-align: center;">
            <a href="clientdash.php?page=cart" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 3rem;">Order Review</a>
        </div>
    <?php endif; ?>
<?php endif; ?>
