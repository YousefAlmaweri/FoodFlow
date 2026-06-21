<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$error   = '';
$success = false;
$order_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address   = trim($_POST['address']   ?? '');
    $payment   =      $_POST['payment']   ?? 'cash';
    $cart_json =      $_POST['cart_data'] ?? '[]';
    $cart      = json_decode($cart_json, true);

    // PHP server-side validation
    if (!$address || strlen($address) < 10) {
        $error = 'Please enter a complete delivery address (at least 10 characters).';
    } elseif (empty($cart) || !is_array($cart)) {
        $error = 'Your cart is empty. Please add items before placing an order.';
    } elseif (!in_array($payment, ['cash','card'], true)) {
        $error = 'Invalid payment method.';
    } else {
        $restaurant_id = (int)($cart[0]['restaurant_id'] ?? 0);
        $delivery_fee  = 5.00;
        $items_total   = 0.0;
        $verified      = [];

        try {
            $pdo->beginTransaction();

            foreach ($cart as $ci) {
                // Always verify price from DB — never trust client
                $s = $pdo->prepare('SELECT `price`,`name` FROM `menu_items` WHERE `id`=? AND `restaurant_id`=? AND `is_available`=1 LIMIT 1');
                $s->execute([(int)$ci['menu_item_id'], $restaurant_id]);
                $db_item = $s->fetch();

                if (!$db_item) {
                    throw new Exception('One or more items are no longer available. Please refresh your cart.');
                }
                $qty          = max(1, (int)$ci['qty']);
                $items_total += $db_item['price'] * $qty;
                $verified[]   = ['menu_item_id' => (int)$ci['menu_item_id'], 'qty' => $qty, 'price' => $db_item['price']];
            }

            $total = $items_total + $delivery_fee;

            $pdo->prepare('INSERT INTO `orders` (`customer_id`,`restaurant_id`,`total_amount`,`delivery_fee`,`delivery_address`,`payment_method`)
                           VALUES (?,?,?,?,?,?)')
                ->execute([$_SESSION['user_id'], $restaurant_id, $total, $delivery_fee, $address, $payment]);

            $order_id = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO `order_items` (`order_id`,`menu_item_id`,`quantity`,`unit_price`,`subtotal`) VALUES (?,?,?,?,?)');
            foreach ($verified as $v) {
                $ins->execute([$order_id, $v['menu_item_id'], $v['qty'], $v['price'], $v['price'] * $v['qty']]);
            }

            $pdo->commit();
            $success = true;

        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<div class="container py-6" style="max-width:860px;">

<?php if ($success): ?>

    <!-- Order Confirmed -->
    <div class="confirm-box">
        <h2 style="color:var(--success);font-size:1.3rem;margin-bottom:6px;">Order Confirmed</h2>
        <h1>Order Placed!</h1>
        <p style="font-size:1.05rem;margin:14px 0;color:var(--text-muted);">
            Order <strong style="color:var(--text);">#<?= $order_id ?></strong> received.<br>
            Your food is being prepared!
        </p>
        <p class="text-muted fs-sm mb-4">Estimated delivery: <strong class="text-primary">30–45 minutes</strong></p>
        <div class="d-flex gap-3 justify-between flex-wrap">
            <a href="orders.php" class="btn btn-lg">View My Orders</a>
            <a href="index.php" class="btn btn-outline btn-lg">Order More</a>
        </div>
    </div>
    <script>
        // Clear cart after successful order
        localStorage.removeItem('ff_cart');
        updateCartBadge();
    </script>

<?php else: ?>

    <h1 class="mb-4">Checkout</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:28px;flex-wrap:wrap;">

        <!-- Form -->
        <form method="POST" action="checkout.php" id="checkout-form" novalidate>
            <input type="hidden" name="cart_data" id="checkout-data" value="[]">

            <div class="card" style="padding:26px;margin-bottom:20px;">
                <h3 class="mb-3">Delivery Details</h3>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="address">Delivery Address <span class="required-star">*</span></label>
                    <textarea id="address" name="address" class="form-control" rows="4" required
                              placeholder="Enter your full delivery address including unit, street, postcode and city..."><?= e($_POST['address'] ?? '') ?></textarea>
                    <span class="field-error" id="address-err"></span>
                </div>
            </div>

            <div class="card" style="padding:26px;margin-bottom:20px;">
                <h3 class="mb-3">Payment Method</h3>
                <div class="form-group" style="margin-bottom:0;">
                    <select name="payment" class="form-control" aria-label="Select payment method">
                        <option value="cash">Cash on Delivery</option>
                        <option value="card">Credit / Debit Card</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-block btn-lg">Place Order</button>
        </form>

        <!-- Order Summary -->
        <div>
            <div class="cart-box sticky-top">
                <h3 class="mb-3">Order Summary</h3>
                <div id="cart-items"></div>
                <div class="d-flex justify-between text-muted fs-sm mt-3">
                    <span>Delivery Fee</span><span>RM 5.00</span>
                </div>
                <div class="cart-total-row">
                    <span>Total</span>
                    <span>RM <span id="cart-total">0.00</span></span>
                </div>
            </div>
        </div>

    </div>

<?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
