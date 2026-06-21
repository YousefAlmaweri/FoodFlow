<?php
$pageTitle = 'Manage Menu';
require_once 'includes/header.php';

if (!isLoggedIn() || !hasRole('partner')) {
    header('Location: auth.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$msg = '';
$msgType = 'success';

// Get this partner's restaurant
$stmt = $pdo->prepare('SELECT * FROM `restaurants` WHERE `partner_id` = ? LIMIT 1');
$stmt->execute([$uid]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: dashboard.php');
    exit;
}

$rid = (int)$restaurant['id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'add_item') {
        $name  = trim($_POST['item_name']  ?? '');
        $price = (float)($_POST['item_price'] ?? 0);
        $desc  = trim($_POST['item_desc']  ?? '');
        $image = trim($_POST['item_image'] ?? '');

        if (!$name) {
            $msg = 'Item name is required.'; $msgType = 'error';
        } elseif ($price <= 0) {
            $msg = 'Price must be greater than 0.'; $msgType = 'error';
        } else {
            if (!$image) $image = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80';
            $pdo->prepare('INSERT INTO `menu_items` (`restaurant_id`,`name`,`description`,`price`,`image`) VALUES (?,?,?,?,?)')
                ->execute([$rid, $name, $desc, $price, $image]);
            $msg = '"' . e($name) . '" added to menu.';
        }
    }

    elseif ($act === 'update_item') {
        $iid   = (int)($_POST['item_id']    ?? 0);
        $name  = trim($_POST['item_name']   ?? '');
        $price = (float)($_POST['item_price'] ?? 0);
        $desc  = trim($_POST['item_desc']   ?? '');
        $image = trim($_POST['item_image']  ?? '');
        if ($iid && $name && $price > 0) {
            $pdo->prepare('UPDATE `menu_items` SET `name`=?,`description`=?,`price`=?,`image`=? WHERE `id`=? AND `restaurant_id`=?')
                ->execute([$name, $desc, $price, $image, $iid, $rid]);
            $msg = 'Item updated.';
        }
    }

    elseif ($act === 'toggle_item') {
        $iid = (int)($_POST['item_id'] ?? 0);
        $cur = (int)($_POST['current'] ?? 1);
        if ($iid) {
            $pdo->prepare('UPDATE `menu_items` SET `is_available`=? WHERE `id`=? AND `restaurant_id`=?')
                ->execute([$cur ? 0 : 1, $iid, $rid]);
            $msg = 'Availability updated.';
        }
    }

    elseif ($act === 'delete_item') {
        $iid = (int)($_POST['item_id'] ?? 0);
        if ($iid) {
            $pdo->prepare('DELETE FROM `menu_items` WHERE `id`=? AND `restaurant_id`=?')
                ->execute([$iid, $rid]);
            $msg = 'Item deleted.';
        }
    }
}

// Fetch menu items
$stmt = $pdo->prepare('SELECT * FROM `menu_items` WHERE `restaurant_id`=? ORDER BY `is_available` DESC, `name` ASC');
$stmt->execute([$rid]);
$menu_items = $stmt->fetchAll();
?>

<div class="container section">

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <div>
            <h1 style="margin-bottom:4px;">Manage Menu</h1>
            <p class="text-muted fs-sm"><?= e($restaurant['name']) ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>"><?= e($msg) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:28px;align-items:flex-start;">

        <!-- Add Item Form -->
        <div>
            <div class="card" style="padding:24px;">
                <h3 class="mb-3">Add New Item</h3>
                <form method="POST" action="menu.php">
                    <input type="hidden" name="action" value="add_item">
                    <div class="form-group">
                        <label>Dish Name <span class="required-star">*</span></label>
                        <input type="text" name="item_name" class="form-control" required placeholder="e.g. Chicken Rice">
                    </div>
                    <div class="form-group">
                        <label>Price (RM) <span class="required-star">*</span></label>
                        <input type="number" name="item_price" class="form-control" required step="0.01" min="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="item_desc" class="form-control" rows="3" placeholder="Brief description of the dish"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="url" name="item_image" class="form-control" placeholder="https://...">
                    </div>
                    <button type="submit" class="btn btn-block">Add Item</button>
                </form>
            </div>
        </div>

        <!-- Item List -->
        <div>
            <h3 class="mb-3">Current Menu (<?= count($menu_items) ?> items)</h3>

            <?php if (empty($menu_items)): ?>
                <div class="card" style="padding:40px;text-align:center;">
                    <p class="text-muted">No menu items yet. Add your first item on the left.</p>
                </div>
            <?php else: ?>
                <?php foreach ($menu_items as $m): ?>
                    <div class="card" style="padding:18px;margin-bottom:14px;<?= $m['is_available'] ? '' : 'opacity:0.65;' ?>">
                        <div class="d-flex gap-3 align-center flex-wrap">
                            <img src="<?= e($m['image'] ?? '') ?>"
                                 alt="<?= e($m['name']) ?>"
                                 style="width:72px;height:72px;border-radius:6px;object-fit:cover;flex-shrink:0;"
                                 onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=80'">
                            <div style="flex:1;min-width:140px;">
                                <strong><?= e($m['name']) ?></strong>
                                <p class="text-muted fs-sm" style="margin:2px 0;"><?= e($m['description'] ?? '') ?></p>
                                <span style="color:var(--primary);font-weight:700;">RM <?= number_format($m['price'], 2) ?></span>
                                <?php if (!$m['is_available']): ?>
                                    <span class="badge badge-rejected" style="margin-left:8px;">Unavailable</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Edit form -->
                        <form method="POST" action="menu.php" style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                            <input type="hidden" name="action"  value="update_item">
                            <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:8px;margin-bottom:8px;">
                                <input type="text"   name="item_name"  value="<?= e($m['name']) ?>"           class="form-control" style="padding:7px;" required placeholder="Name">
                                <input type="number" name="item_price" value="<?= number_format($m['price'],2) ?>" class="form-control" style="padding:7px;" step="0.01" min="0.01" required placeholder="Price">
                                <input type="url"    name="item_image" value="<?= e($m['image'] ?? '') ?>"    class="form-control" style="padding:7px;" placeholder="Image URL">
                            </div>
                            <input type="text" name="item_desc" value="<?= e($m['description'] ?? '') ?>" class="form-control" style="padding:7px;margin-bottom:8px;" placeholder="Description">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-sm">Save Changes</button>
                        </form>
                                <!-- Toggle and Delete in separate forms -->
                                <form method="POST" action="menu.php" style="display:inline;">
                                    <input type="hidden" name="action"  value="toggle_item">
                                    <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="current" value="<?= (int)$m['is_available'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?= $m['is_available'] ? 'Mark Unavailable' : 'Mark Available' ?>
                                    </button>
                                </form>
                                <form method="POST" action="menu.php" onsubmit="return confirm('Delete this menu item permanently?');" style="display:inline;">
                                    <input type="hidden" name="action"  value="delete_item">
                                    <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
