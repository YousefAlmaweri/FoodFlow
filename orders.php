<?php
$pageTitle = 'My Orders';
require_once 'includes/header.php';

if (!isLoggedIn() || !hasRole('customer')) {
    header('Location: auth.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Fetch all orders for this customer
$stmt = $pdo->prepare(
    'SELECT o.*, r.name AS restaurant_name, r.image AS restaurant_image
     FROM `orders` o
     JOIN `restaurants` r ON o.restaurant_id = r.id
     WHERE o.customer_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$uid]);
$orders = $stmt->fetchAll();
?>

<div class="container section">

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <h1>My Orders</h1>
        <a href="index.php" class="btn btn-outline btn-sm">Order More Food</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card" style="padding:60px;text-align:center;">
            <h3 class="mb-3" style="color:var(--text-muted);">No orders yet</h3>
            <p class="text-muted mb-4">You have not placed any orders. Browse restaurants to get started!</p>
            <a href="index.php" class="btn btn-lg">Browse Restaurants</a>
        </div>
    <?php else: ?>

        <div style="display:flex;flex-direction:column;gap:20px;">
            <?php foreach ($orders as $o):
                // Fetch order items for this order
                $items = $pdo->prepare(
                    'SELECT oi.*, m.name AS item_name
                     FROM `order_items` oi
                     JOIN `menu_items` m ON oi.menu_item_id = m.id
                     WHERE oi.order_id = ?'
                );
                $items->execute([$o['id']]);
                $items = $items->fetchAll();
            ?>
                <div class="card" style="padding:24px;">
                    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
                        <div>
                            <h3 style="margin-bottom:4px;">Order #<?= $o['id'] ?></h3>
                            <span class="text-muted fs-sm"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></span>
                        </div>
                        <div class="d-flex align-center gap-2 flex-wrap">
                            <span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span>
                            <span class="badge" style="background:var(--bg-input);color:var(--text-muted);"><?= ucfirst(e($o['payment_method'])) ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-center gap-3 mb-3 flex-wrap">
                        <img src="<?= e($o['restaurant_image']) ?>"
                             alt="<?= e($o['restaurant_name']) ?>"
                             style="width:48px;height:48px;border-radius:6px;object-fit:cover;flex-shrink:0;"
                             onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100&q=80'">
                        <div>
                            <strong><?= e($o['restaurant_name']) ?></strong>
                            <p class="text-muted fs-sm" style="margin-top:2px;"><?= e($o['delivery_address']) ?></p>
                        </div>
                    </div>

                    <!-- Items list -->
                    <?php if (!empty($items)): ?>
                        <div style="background:var(--bg-input);border-radius:8px;padding:14px;margin-bottom:16px;">
                            <?php foreach ($items as $item): ?>
                                <div class="d-flex justify-between fs-sm" style="padding:4px 0;border-bottom:1px solid var(--border);">
                                    <span><?= e($item['item_name']) ?> x<?= (int)$item['quantity'] ?></span>
                                    <span style="font-weight:600;">RM <?= number_format($item['subtotal'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="d-flex justify-between fs-sm" style="padding:6px 0;color:var(--text-muted);">
                                <span>Delivery Fee</span>
                                <span>RM <?= number_format($o['delivery_fee'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-between" style="padding-top:8px;border-top:2px solid var(--border);font-weight:700;">
                                <span>Total</span>
                                <span style="color:var(--primary);">RM <?= number_format($o['total_amount'], 2) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="restaurant.php?id=<?= (int)$o['restaurant_id'] ?>" class="btn btn-sm">Reorder</a>
                        <?php if ($o['status'] === 'pending'): ?>
                            <span class="btn btn-outline btn-sm" style="cursor:default;pointer-events:none;">Awaiting Confirmation</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
