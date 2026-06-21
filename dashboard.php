<?php
require_once 'config.php';
if (!isLoggedIn()) { header('Location: auth.php'); exit; }

$role    = $_SESSION['user_role'];
$uid     = (int)$_SESSION['user_id'];
$msg     = '';
$msgType = 'success';

// 
//  POST ACTIONS
// 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    //  PARTNER 
    if ($role === 'partner') {

        if ($act === 'setup_restaurant') {
            $name    = trim($_POST['name']    ?? '');
            $cuisine = trim($_POST['cuisine'] ?? '');
            $image   = trim($_POST['image']   ?? 'https://picsum.photos/seed/rest/800/400');
            if ($name && $cuisine) {
                $pdo->prepare('INSERT INTO `restaurants` (`partner_id`,`name`,`image`,`cuisine`) VALUES (?,?,?,?)')
                    ->execute([$uid, $name, $image, $cuisine]);
                $msg = 'Restaurant created!';
            } else { $msg = 'Name and cuisine are required.'; $msgType = 'error'; }
        }

        elseif ($act === 'update_restaurant') {
            $name    = trim($_POST['name']    ?? '');
            $cuisine = trim($_POST['cuisine'] ?? '');
            $image   = trim($_POST['image']   ?? '');
            if ($name && $cuisine) {
                $pdo->prepare('UPDATE `restaurants` SET `name`=?,`cuisine`=?,`image`=? WHERE `partner_id`=?')
                    ->execute([$name, $cuisine, $image, $uid]);
                $msg = 'Restaurant updated.';
            }
        }

        elseif ($act === 'add_item') {
            $s = $pdo->prepare('SELECT `id` FROM `restaurants` WHERE `partner_id`=? LIMIT 1');
            $s->execute([$uid]); $res = $s->fetch();
            if ($res) {
                $n = trim($_POST['item_name'] ?? ''); $p = (float)($_POST['item_price'] ?? 0);
                $d = trim($_POST['item_desc'] ?? '');
                if ($n && $p > 0) {
                    $pdo->prepare('INSERT INTO `menu_items` (`restaurant_id`,`name`,`description`,`price`,`image`) VALUES (?,?,?,?,?)')
                        ->execute([$res['id'], $n, $d, $p, 'https://picsum.photos/seed/' . urlencode($n) . '/400/300']);
                    $msg = '"' . e($n) . '" added to menu.';
                } else { $msg = 'Item name and price are required.'; $msgType = 'error'; }
            }
        }

        elseif ($act === 'update_item') {
            $iid = (int)($_POST['item_id'] ?? 0);
            $n   = trim($_POST['item_name'] ?? ''); $p = (float)($_POST['item_price'] ?? 0);
            $img = trim($_POST['item_image'] ?? '');
            if ($iid && $n && $p > 0) {
                $pdo->prepare('UPDATE `menu_items` SET `name`=?,`price`=?,`image`=? WHERE `id`=?')
                    ->execute([$n, $p, $img, $iid]);
                $msg = 'Item updated.';
            }
        }

        elseif ($act === 'delete_item') {
            $iid = (int)($_POST['item_id'] ?? 0);
            if ($iid) { $pdo->prepare('DELETE FROM `menu_items` WHERE `id`=?')->execute([$iid]); $msg = 'Item deleted.'; }
        }

        elseif ($act === 'toggle_item') {
            $iid = (int)($_POST['item_id'] ?? 0); $cur = (int)($_POST['current'] ?? 1);
            if ($iid) { $pdo->prepare('UPDATE `menu_items` SET `is_available`=? WHERE `id`=?')->execute([$cur ? 0 : 1, $iid]); $msg = 'Availability updated.'; }
        }

        elseif ($act === 'order_status') {
            $oid = (int)($_POST['order_id'] ?? 0); $st = $_POST['status'] ?? '';
            $allowed = ['pending','accepted','rejected','picking_up','delivering','delivered'];
            if ($oid && in_array($st, $allowed, true)) {
                $pdo->prepare('UPDATE `orders` SET `status`=? WHERE `id`=?')->execute([$st, $oid]);
                $msg = 'Order #' . $oid . ' updated.';
            }
        }
    }

    //  ADMIN 
    if ($role === 'admin') {

        if ($act === 'admin_order_status') {
            $oid = (int)($_POST['order_id'] ?? 0); $st = $_POST['status'] ?? '';
            $allowed = ['pending','accepted','rejected','picking_up','delivering','delivered'];
            if ($oid && in_array($st, $allowed, true)) {
                $pdo->prepare('UPDATE `orders` SET `status`=? WHERE `id`=?')->execute([$st, $oid]);
                $msg = 'Order #' . $oid . ' updated.';
            }
        }

        elseif ($act === 'change_role') {
            $tid  = (int)($_POST['target_id'] ?? 0); $nr = $_POST['new_role'] ?? '';
            $allowed = ['customer','partner','admin','rider'];
            if ($tid && in_array($nr, $allowed, true) && $tid !== $uid) {
                $pdo->prepare('UPDATE `users` SET `role`=? WHERE `id`=?')->execute([$nr, $tid]);
                $msg = 'User role updated.';
            } else { $msg = 'Cannot change your own role.'; $msgType = 'error'; }
        }

        elseif ($act === 'delete_user') {
            $tid = (int)($_POST['target_id'] ?? 0);
            if ($tid && $tid !== $uid) {
                $pdo->prepare('DELETE FROM `users` WHERE `id`=?')->execute([$tid]);
                $msg = 'User deleted.';
            } else { $msg = 'Cannot delete your own account.'; $msgType = 'error'; }
        }

        elseif ($act === 'delete_restaurant') {
            $rid = (int)($_POST['restaurant_id'] ?? 0);
            if ($rid) { $pdo->prepare('DELETE FROM `restaurants` WHERE `id`=?')->execute([$rid]); $msg = 'Restaurant deleted.'; }
        }

        elseif ($act === 'update_restaurant') {
            $rid = (int)($_POST['restaurant_id'] ?? 0); $n = trim($_POST['name'] ?? '');
            if ($rid && $n) { $pdo->prepare('UPDATE `restaurants` SET `name`=? WHERE `id`=?')->execute([$n, $rid]); $msg = 'Restaurant updated.'; }
        }
    }
}

$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<div class="container section">

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <h1>Dashboard</h1>
        <div class="d-flex gap-2 flex-wrap align-center">
            <span class="badge badge-<?= e($role) ?>" style="font-size:0.9rem;padding:8px 16px;">
                <?= e($_SESSION['user_name']) ?> &mdash; <?= ucfirst($role) ?>
            </span>
            <?php if ($role === 'customer'): ?>
                <a href="orders.php" class="btn btn-outline btn-sm">View Full Order History</a>
            <?php endif; ?>
            <?php if ($role === 'partner'): ?>
                <a href="menu.php" class="btn btn-outline btn-sm">Manage Menu</a>
            <?php endif; ?>
            <?php if ($role === 'admin'): ?>
                <a href="reports.php" class="btn btn-outline btn-sm">Analytics &amp; Reports</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>"><?= e($msg) ?></div>
    <?php endif; ?>

    <?php /*  CUSTOMER  */ ?>
    <?php if ($role === 'customer'): ?>

        <h2 class="mb-4">My Orders</h2>
        <?php
        $orders = $pdo->prepare('SELECT o.*,r.name AS rname FROM `orders` o
                                  JOIN `restaurants` r ON o.restaurant_id=r.id
                                  WHERE o.customer_id=? ORDER BY o.created_at DESC');
        $orders->execute([$uid]); $orders = $orders->fetchAll();
        ?>
        <div class="card table-wrap">
            <?php if (empty($orders)): ?>
                <p class="text-muted text-center" style="padding:40px;">
                    No orders yet. <a href="index.php">Browse restaurants!</a>
                </p>
            <?php else: ?>
                <table class="table">
                    <thead><tr>
                        <th>#</th><th>Restaurant</th><th>Total</th>
                        <th>Payment</th><th>Date</th><th>Status</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= e($o['rname']) ?></td>
                            <td>RM <?= number_format($o['total_amount'],2) ?></td>
                            <td><?= ucfirst(e($o['payment_method'])) ?></td>
                            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                            <td><a href="restaurant.php?id=<?= (int)$o['restaurant_id'] ?>" class="btn btn-sm btn-outline">Reorder</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php /*  PARTNER  */ ?>
    <?php elseif ($role === 'partner'): ?>

        <?php
        $s = $pdo->prepare('SELECT * FROM `restaurants` WHERE `partner_id`=? LIMIT 1');
        $s->execute([$uid]); $rest = $s->fetch();
        ?>

        <?php if (!$rest): ?>
            <div class="card" style="max-width:560px;padding:32px;">
                <h2 class="mb-4">Set Up Your Restaurant</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="setup_restaurant">
                    <div class="form-group">
                        <label>Restaurant Name <span class="required-star">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. My Cafe">
                    </div>
                    <div class="form-group">
                        <label>Cuisine Type <span class="required-star">*</span></label>
                        <select name="cuisine" class="form-control">
                            <option value="Local Cuisine">Local Cuisine</option>
                            <option value="Western & Fast Food">Western &amp; Fast Food</option>
                            <option value="Healthy & Diet Meals">Healthy &amp; Diet Meals</option>
                            <option value="Beverages & Desserts">Beverages &amp; Desserts</option>
                            <option value="Snacks & Groceries">Snacks &amp; Groceries</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cover Image URL</label>
                        <input type="url" name="image" class="form-control" placeholder="https://...">
                    </div>
                    <button type="submit" class="btn">Create Restaurant</button>
                </form>
            </div>

        <?php else:
            $rid = (int)$rest['id'];
            $incoming = $pdo->prepare('SELECT o.*,u.name AS cname FROM `orders` o
                                       JOIN `users` u ON o.customer_id=u.id
                                       WHERE o.restaurant_id=? ORDER BY o.created_at DESC LIMIT 20');
            $incoming->execute([$rid]); $incoming = $incoming->fetchAll();
            $menu_items = $pdo->prepare('SELECT * FROM `menu_items` WHERE `restaurant_id`=? ORDER BY `name`');
            $menu_items->execute([$rid]); $menu_items = $menu_items->fetchAll();
        ?>

            <!-- Restaurant Settings -->
            <div class="card mb-4" style="padding:22px;">
                <h3 class="mb-3">Restaurant Settings</h3>
                <form method="POST" class="d-flex gap-3 flex-wrap align-center">
                    <input type="hidden" name="action" value="update_restaurant">
                    <input type="text" name="name"    value="<?= e($rest['name']) ?>"    class="form-control" style="flex:2;min-width:160px;" placeholder="Name" required>
                    <select name="cuisine" class="form-control" style="flex:2;min-width:160px;">
                        <?php foreach(['Local Cuisine','Western & Fast Food','Healthy & Diet Meals','Beverages & Desserts','Snacks & Groceries'] as $c): ?>
                            <option value="<?= e($c) ?>" <?= $rest['cuisine']===$c?'selected':'' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="url"  name="image"   value="<?= e($rest['image']) ?>"   class="form-control" style="flex:3;min-width:200px;" placeholder="Cover Image URL">
                    <button type="submit" class="btn btn-sm">Save</button>
                </form>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;flex-wrap:wrap;">

                <!-- Incoming Orders -->
                <div>
                    <h2 class="mb-3">Incoming Orders</h2>
                    <div class="card table-wrap" style="padding:16px;">
                        <?php if (empty($incoming)): ?>
                            <p class="text-muted fs-sm">No orders yet.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($incoming as $o): ?>
                                    <tr>
                                        <td>#<?= $o['id'] ?></td>
                                        <td><?= e($o['cname']) ?></td>
                                        <td>RM <?= number_format($o['total_amount'],2) ?></td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="action"   value="order_status">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control" style="padding:4px 6px;width:auto;">
                                                    <?php foreach(['pending','accepted','picking_up','delivering','delivered','rejected'] as $st): ?>
                                                        <option value="<?= $st ?>" <?= $o['status']===$st?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Manage Menu -->
                <div>
                    <h2 class="mb-3">Manage Menu</h2>

                    <!-- Add item -->
                    <div class="card mb-3" style="padding:18px;">
                        <h4 class="mb-3">Add New Item</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_item">
                            <div class="d-flex gap-2 mb-2">
                                <input type="text"   name="item_name"  class="form-control" placeholder="Dish name" required style="flex:2;">
                                <input type="number" name="item_price" class="form-control" placeholder="RM" required step="0.01" min="0.01" style="flex:1;">
                            </div>
                            <input type="text" name="item_desc" class="form-control mb-2" placeholder="Description (optional)">
                            <button class="btn btn-sm">Add Item</button>
                        </form>
                    </div>

                    <!-- Edit items -->
                    <div class="card" style="padding:18px;max-height:440px;overflow-y:auto;">
                        <?php if (empty($menu_items)): ?>
                            <p class="text-muted fs-sm">No items yet.</p>
                        <?php else: ?>
                            <?php foreach ($menu_items as $m): ?>
                                <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                                    <form method="POST" class="d-flex gap-2 flex-wrap mb-2">
                                        <input type="hidden" name="action"     value="update_item">
                                        <input type="hidden" name="item_id"    value="<?= $m['id'] ?>">
                                        <input type="text"   name="item_name"  value="<?= e($m['name']) ?>"  class="form-control" style="flex:2;padding:6px;" required>
                                        <input type="number" name="item_price" value="<?= $m['price'] ?>" class="form-control" style="width:80px;padding:6px;" step="0.01" min="0.01" required>
                                        <input type="url"    name="item_image" value="<?= e($m['image']??'') ?>" class="form-control" style="flex:2;padding:6px;" placeholder="Image URL">
                                        <button type="submit" class="btn btn-sm">Save</button>
                                    </form>
                                    <div class="d-flex gap-2">
                                        <form method="POST">
                                            <input type="hidden" name="action"  value="toggle_item">
                                            <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="current" value="<?= (int)$m['is_available'] ?>">
                                            <button class="btn btn-outline btn-sm"><?= $m['is_available'] ? 'Mark Unavailable' : 'Mark Available' ?></button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Delete this item?')">
                                            <input type="hidden" name="action"  value="delete_item">
                                            <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; // rest exists ?>

    <?php /*  ADMIN  */ ?>
    <?php elseif ($role === 'admin'): ?>

        <?php
        $stats = [
            'users'    => $pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn(),
            'rests'    => $pdo->query('SELECT COUNT(*) FROM `restaurants`')->fetchColumn(),
            'orders'   => $pdo->query('SELECT COUNT(*) FROM `orders`')->fetchColumn(),
            'revenue'  => $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM `orders` WHERE status='delivered'")->fetchColumn(),
        ];
        ?>

        <div class="stat-grid mb-4">
            <div class="stat-card"><div class="stat-label">Total Users</div>      <div class="stat-value"><?= $stats['users'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Restaurants</div>      <div class="stat-value"><?= $stats['rests'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Total Orders</div>     <div class="stat-value"><?= $stats['orders'] ?></div></div>
            <div class="stat-card"><div class="stat-label">Revenue (Delivered)</div><div class="stat-value">RM <?= number_format($stats['revenue'],2) ?></div></div>
        </div>

        <!-- All Orders -->
        <h2 class="mb-3">All Orders</h2>
        <?php
        $all_orders = $pdo->query('SELECT o.*,u.name AS cname,r.name AS rname
                                   FROM `orders` o JOIN `users` u ON o.customer_id=u.id
                                   JOIN `restaurants` r ON o.restaurant_id=r.id
                                   ORDER BY o.created_at DESC LIMIT 30')->fetchAll();
        ?>
        <div class="card table-wrap mb-4" style="padding:16px;">
            <table class="table">
                <thead><tr><th>#</th><th>Customer</th><th>Restaurant</th><th>Total</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
                <tbody>
                <?php foreach ($all_orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= e($o['cname']) ?></td>
                        <td><?= e($o['rname']) ?></td>
                        <td>RM <?= number_format($o['total_amount'],2) ?></td>
                        <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                        <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="action"   value="admin_order_status">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="form-control" style="padding:4px 6px;width:auto;">
                                    <?php foreach(['pending','accepted','picking_up','delivering','delivered','rejected'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $o['status']===$st?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Users -->
        <h2 class="mb-3">Manage Users</h2>
        <?php $all_users = $pdo->query('SELECT * FROM `users` ORDER BY `created_at` DESC')->fetchAll(); ?>
        <div class="card table-wrap mb-4" style="padding:16px;">
            <table class="table">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($all_users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2 align-center">
                                <input type="hidden" name="action"    value="change_role">
                                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                <select name="new_role" class="form-control" style="padding:4px 6px;width:auto;">
                                    <?php foreach(['customer','partner','admin','rider'] as $ro): ?>
                                        <option value="<?= $ro ?>" <?= $u['role']===$ro?'selected':'' ?>><?= ucfirst($ro) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm">Save</button>
                            </form>
                        </td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] !== $uid): ?>
                                <form method="POST" onsubmit="return confirm('Delete <?= e(addslashes($u['name'])) ?>?')">
                                    <input type="hidden" name="action"    value="delete_user">
                                    <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted fs-sm">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Restaurants -->
        <h2 class="mb-3">Manage Restaurants</h2>
        <?php $all_rests = $pdo->query('SELECT r.*,u.name AS pname FROM `restaurants` r JOIN `users` u ON r.partner_id=u.id ORDER BY r.id DESC')->fetchAll(); ?>
        <div class="card table-wrap" style="padding:16px;">
            <table class="table">
                <thead><tr><th>ID</th><th>Partner</th><th>Name</th><th>Cuisine</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($all_rests as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= e($r['pname']) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2 align-center">
                                <input type="hidden" name="action"        value="update_restaurant">
                                <input type="hidden" name="restaurant_id" value="<?= $r['id'] ?>">
                                <input type="text" name="name" value="<?= e($r['name']) ?>" class="form-control" style="padding:6px;" required>
                                <button class="btn btn-sm">Save</button>
                            </form>
                        </td>
                        <td><?= e($r['cuisine']) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this restaurant?')">
                                <input type="hidden" name="action"        value="delete_restaurant">
                                <input type="hidden" name="restaurant_id" value="<?= $r['id'] ?>">
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
