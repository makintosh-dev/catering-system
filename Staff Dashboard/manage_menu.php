<?php
// Ensure this file is included from within the admin dashboard
if (!isset($page) || $page !== 'menus') {
    exit;
}
?>

<?php foreach ($menusList as $m): ?>
<div class="card">
    <h2 style="color: var(--primary-hover); margin-bottom: 0.5rem;"><?= htmlspecialchars($m['package']['name']) ?></h2>
    <p style="color: var(--text-muted); margin-bottom: 1rem;"><?= htmlspecialchars($m['package']['description']) ?></p>
    
    <table style="max-width: 600px;">
        <thead>
            <tr><th>Category</th><th>Item Name</th><th>Price (per head)</th></tr>
        </thead>
        <tbody>
            <?php foreach ($m['items'] as $item): ?>
            <tr>
                <td><span style="background:var(--bg-main); padding: 0.2rem 0.5rem; border-radius:4px; font-size:0.8rem; border:1px solid var(--border-color);"><?= htmlspecialchars($item['category']) ?></span></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
