<?php
$pageTitle = 'Menu';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo '<div class="container py-6"><div class="alert alert-error">Invalid restaurant.</div>
          <a href="index.php" class="btn mt-3">Back to Restaurants</a></div>';
    require_once 'includes/footer.php'; exit;
}

$stmt = $pdo->prepare('SELECT * FROM `restaurants` WHERE `id` = ? LIMIT 1');
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    echo '<div class="container py-6"><div class="alert alert-error">Restaurant not found.</div>
          <a href="index.php" class="btn mt-3">Back to Restaurants</a></div>';
    require_once 'includes/footer.php'; exit;
}

$stmt = $pdo->prepare('SELECT * FROM `menu_items` WHERE `restaurant_id` = ? ORDER BY `is_available` DESC, `name` ASC');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$pageTitle = e($r['name']);
?>

<!-- Restaurant Banner -->
<div style="background:linear-gradient(rgba(18,18,18,.78),rgba(18,18,18,.92)),
            url('<?= e($r['image']) ?>') center/cover;
            padding:56px 0;border-bottom:1px solid var(--border);">
    <div class="container d-flex align-center gap-4 flex-wrap">
        <img src="<?= e($r['image']) ?>"
             alt="<?= e($r['name']) ?>"
             style="width:100px;height:100px;border-radius:var(--radius);object-fit:cover;border:3px solid var(--primary);flex-shrink:0;"
             onerror="this.src='https://picsum.photos/seed/<?= $id ?>/200/200'">
        <div>
            <h1 style="margin-bottom:10px;"><?= e($r['name']) ?></h1>
            <div class="d-flex gap-3 flex-wrap text-muted fs-sm">
                <span><?= number_format($r['rating'],1) ?> </span>
                <span> <?= e($r['cuisine']) ?></span>
                <span><?= (int)$r['est_delivery_time'] ?> min delivery</span>
                <span>Min RM <?= number_format($r['min_order'],2) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="container section">
    <div class="d-flex gap-4 align-center flex-wrap" style="align-items:flex-start;">

        <!-- Menu Items -->
        <div style="flex:1;min-width:0;">
            <h2 class="mb-4">Menu</h2>

            <?php if (empty($items)): ?>
                <p class="text-muted">No menu items available at this time.</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="card menu-row" style="border-radius:var(--radius);margin-bottom:14px;padding:16px;">
                        <img src="<?= e($item['image'] ?: 'https://picsum.photos/seed/' . $item['id'] . '/200/200') ?>"
                             alt="<?= e($item['name']) ?>"
                             onerror="this.src='https://picsum.photos/seed/<?= (int)$item['id'] ?>/200/200'">
                        <div class="menu-info">
                            <h4><?= e($item['name']) ?></h4>
                            <p><?= e($item['description'] ?? '') ?></p>
                            <span class="card-price fw-700">RM <?= number_format($item['price'],2) ?></span>
                        </div>
                        <?php if ($item['is_available']): ?>
                            <button class="btn btn-sm" style="flex-shrink:0;"
                                onclick='addToCart(<?= json_encode([
                                    "restaurant_id" => (int)$r["id"],
                                    "menu_item_id"  => (int)$item["id"],
                                    "name"          => $item["name"],
                                    "price"         => (float)$item["price"]
                                ], JSON_HEX_APOS) ?>)'
                                aria-label="Add <?= e($item['name']) ?> to cart">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <span class="badge badge-rejected">Unavailable</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Cart Sidebar -->
        <aside style="width:300px;flex-shrink:0;" class="sticky-top">
            <div class="cart-box">
                <h3 class="mb-3">Your Order</h3>
                <div id="cart-items"></div>
                <div class="cart-total-row mt-3">
                    <span>Subtotal</span>
                    <span>RM <span id="cart-total">0.00</span></span>
                </div>
                <p class="text-muted fs-sm mt-1">+ RM 5.00 delivery fee at checkout</p>
                <a href="checkout.php" class="btn btn-block mt-3" style="text-align:center;">
                    Proceed to Checkout
                </a>
            </div>
        </aside>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
