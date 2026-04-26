<?php
// Ensure this file is included from within the client dashboard
if (!isset($page) || $page !== 'cart') {
    exit;
}
?>

<div class="cart-summary">
    <div class="card cart-items">
        <h2>Your Cart</h2>
        <?php if (empty($cartDetails)): ?>
            <p style="margin-top:1rem;">Your cart is empty. <a href="clientdash.php?page=menu" style="color:var(--primary-hover);">Browse our menu</a>.</p>
        <?php else: ?>
            <table style="margin-top:1rem;">
                <thead>
                    <tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cartDetails as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                        <td>PKR <?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>PKR <?= number_format($item['total'], 2) ?></td>
                        <td>
                            <form action="clientdash.php?page=cart" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="remove_cart">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size:0.8rem;">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartDetails)): ?>
    <div class="checkout-box">
        <h3>Order Summary</h3>

        <div style="margin-top:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color); font-size:0.9rem;">
            <strong>Event:</strong> <?= htmlspecialchars($_SESSION['event']['type']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($_SESSION['event']['date']) ?><br>
            <strong>Guests:</strong> <?= htmlspecialchars($_SESSION['event']['guest_count']) ?>
        </div>

        <div style="margin-top:1rem; display:flex; justify-content:space-between; color:var(--text-muted);">
            <span>Items (<?= $cartCount ?>)</span>
            <span>PKR <?= number_format($cartTotal, 2) ?></span>
        </div>
        <div class="checkout-total">
            <span>Total:</span>
            <span>PKR <?= number_format($cartTotal, 2) ?></span>
        </div>

        <form action="controllers/client_dashboard/events.php" method="POST">
            <input type="hidden" name="action" value="checkout">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Payment Method</label>
                <select name="payment_method" class="form-control" required style="padding: 0.5rem; font-size: 0.95rem;">
                    <option value="">Select a method...</option>
                    <option value="card">Credit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cash">Cash on Delivery / On Site</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; text-align:center; padding: 1rem;">Confirm &amp; Place Order</button>
        </form>
    </div>
    <?php endif; ?>
</div>
