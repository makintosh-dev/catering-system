<?php
// Ensure this file is included from within the admin dashboard
if (!isset($page) || $page !== 'menus') {
    exit;
}
?>

<!-- Form to Create New Package/Menu -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem;">Create New Package/Menu</h2>
    <form action="controllers/staff_dashboard/menu_actions.php" method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
        <input type="hidden" name="action" value="create_menu">
        <div style="flex: 1;">
            <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Package Name</label>
            <input type="text" name="name" required style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 8px;">
        </div>
        <div style="flex: 2;">
            <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Description</label>
            <input type="text" name="description" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 8px;">
        </div>
        <button type="submit" class="btn" style="width: auto; padding: 0.6rem 1.5rem; margin-top: 0;">Create</button>
    </form>
</div>

<?php foreach ($menusList as $m): ?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
        <div>
            <h2 style="color: var(--primary-hover); margin-bottom: 0.5rem;"><?= htmlspecialchars($m['package']['name']) ?></h2>
            <p style="color: var(--text-muted);"><?= htmlspecialchars($m['package']['description']) ?></p>
        </div>
        <form action="controllers/staff_dashboard/menu_actions.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this entire package? All items inside will be lost.');">
            <input type="hidden" name="action" value="delete_menu">
            <input type="hidden" name="menu_id" value="<?= $m['package']['id'] ?>">
            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.85rem; font-weight: 600; padding: 0.5rem; border-radius: 6px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='none'">Delete Package</button>
        </form>
    </div>
    
    <table style="width: 100%; margin-bottom: 1.5rem;">
        <thead>
            <tr>
                <th style="width: 20%;">Category</th>
                <th style="width: 40%;">Item Name</th>
                <th style="width: 20%;">Price (per head)</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($m['items'] as $item): ?>
            <tr>
                <td><span style="background:var(--bg-main); padding: 0.2rem 0.5rem; border-radius:4px; font-size:0.8rem; border:1px solid var(--border-color);"><?= htmlspecialchars($item['category']) ?></span></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>PKR <?= number_format($item['price'], 2) ?></td>
                <td>
                    <form action="controllers/staff_dashboard/menu_actions.php" method="POST" style="display: inline;" onsubmit="return confirm('Remove this item from the package?');">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;" title="Remove Item">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Add Item Form -->
    <div style="background: var(--bg-main); padding: 1rem; border-radius: 12px; border: 1px dashed var(--border-color);">
        <h4 style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--text-dark);">Add Item to <?= htmlspecialchars($m['package']['name']) ?></h4>
        <form action="controllers/staff_dashboard/menu_actions.php" method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="action" value="add_item">
            <input type="hidden" name="menu_id" value="<?= $m['package']['id'] ?>">
            
            <div style="flex: 2;">
                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Item Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
            </div>
            
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Category</label>
                <select name="category_id" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem; background: white;">
                    <option value="">Select...</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Price ($)</label>
                <input type="number" step="0.01" min="0" name="price" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
            </div>
            
            <button type="submit" style="background: var(--text-dark); color: white; border: none; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.85rem; cursor: pointer; height: 32px;">Add</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
