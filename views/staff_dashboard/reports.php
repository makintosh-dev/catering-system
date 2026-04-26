<?php
if (!isset($page) || $page !== 'reports') {
    exit;
}

// Prepare data for Chart.js
$revenueLabels = [];
$revenueData = [];
foreach ($reportData['revenue'] as $r) {
    $revenueLabels[] = $r['month'];
    $revenueData[] = (float)$r['total'];
}

$statusLabels = [];
$statusData = [];
$statusColors = [];
$colorMap = [
    'pending' => '#f59e0b',
    'confirmed' => '#3b82f6',
    'in_progress' => '#8b5cf6',
    'delivered' => '#10b981',
    'cancelled' => '#ef4444'
];

foreach ($reportData['status_distribution'] as $s) {
    $statusLabels[] = ucfirst(str_replace('_', ' ', $s['status']));
    $statusData[] = (int)$s['count'];
    $statusColors[] = $colorMap[$s['status']] ?? '#9ca3af';
}
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Revenue Chart -->
    <div class="card">
        <h2 style="margin-bottom: 1rem; color: var(--primary-hover);">Monthly Revenue Trend</h2>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Order Status Chart -->
    <div class="card">
        <h2 style="margin-bottom: 1rem; color: var(--primary-hover);">Order Status Distribution</h2>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Items Table -->
<div class="card">
    <h2 style="margin-bottom: 1rem; color: var(--primary-hover);">Top Selling Items</h2>
    <?php if (empty($reportData['top_items'])): ?>
        <p style="color: var(--text-muted);">No sales data available yet.</p>
    <?php else: ?>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Package / Menu</th>
                    <th>Category</th>
                    <th>Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData['top_items'] as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['menu_name']) ?></td>
                    <td><span style="background:var(--bg-main); padding: 0.2rem 0.5rem; border-radius:4px; font-size:0.8rem; border:1px solid var(--border-color);"><?= htmlspecialchars($item['category']) ?></span></td>
                    <td><?= $item['total_qty_ordered'] ?></td>
                    <td style="color: var(--success); font-weight: 600;">PKR <?= number_format($item['total_revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Line Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($revenueLabels) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#d4af37',
                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4, // smooth curve
                pointBackgroundColor: '#1e293b',
                pointBorderColor: '#d4af37',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusData) ?>,
                backgroundColor: <?= json_encode($statusColors) ?>,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
            }
        }
    });
});
</script>
