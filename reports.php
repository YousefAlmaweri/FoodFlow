<?php
$pageTitle = 'Analytics & Reports';
require_once 'includes/header.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: auth.php');
    exit;
}

// Platform stats
$totalUsers       = (int)$pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn();
$totalRestaurants = (int)$pdo->query('SELECT COUNT(*) FROM `restaurants`')->fetchColumn();
$totalOrders      = (int)$pdo->query('SELECT COUNT(*) FROM `orders`')->fetchColumn();
$totalRevenue     = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM `orders` WHERE status='delivered'")->fetchColumn();
$pendingOrders    = (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE status='pending'")->fetchColumn();
$deliveredOrders  = (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE status='delivered'")->fetchColumn();

// Orders by status
$statusRows = $pdo->query("SELECT status, COUNT(*) as cnt FROM `orders` GROUP BY status ORDER BY cnt DESC")->fetchAll();

// Top restaurants by orders
$topRestaurants = $pdo->query(
    'SELECT r.name, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as revenue
     FROM `restaurants` r
     LEFT JOIN `orders` o ON r.id = o.restaurant_id
     GROUP BY r.id, r.name
     ORDER BY order_count DESC
     LIMIT 5'
)->fetchAll();

// Recent orders
$recentOrders = $pdo->query(
    'SELECT o.*, u.name AS cname, r.name AS rname
     FROM `orders` o
     JOIN `users` u ON o.customer_id = u.id
     JOIN `restaurants` r ON o.restaurant_id = r.id
     ORDER BY o.created_at DESC
     LIMIT 10'
)->fetchAll();

// Users by role
$roleRows = $pdo->query("SELECT role, COUNT(*) as cnt FROM `users` GROUP BY role ORDER BY cnt DESC")->fetchAll();
?>

<div class="container section">

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <h1>Analytics &amp; Reports</h1>
        <a href="dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a>
    </div>

    <!-- Summary Stats -->
    <div class="stat-grid mb-4">
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?= $totalUsers ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Restaurants</div>
            <div class="stat-value"><?= $totalRestaurants ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= $totalOrders ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Delivered Revenue</div>
            <div class="stat-value" style="font-size:1.4rem;">RM <?= number_format($totalRevenue, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pendingOrders ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Delivered Orders</div>
            <div class="stat-value" style="color:var(--success);"><?= $deliveredOrders ?></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:28px;">

        <!-- Orders by Status -->
        <div class="card" style="padding:24px;">
            <h3 class="mb-3">Orders by Status</h3>
            <table class="table" style="margin-top:0;">
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach ($statusRows as $row): ?>
                        <tr>
                            <td><span class="badge badge-<?= e($row['status']) ?>"><?= ucfirst(str_replace('_',' ',$row['status'])) ?></span></td>
                            <td><strong><?= (int)$row['cnt'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($statusRows)): ?>
                        <tr><td colspan="2" class="text-muted">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Users by Role -->
        <div class="card" style="padding:24px;">
            <h3 class="mb-3">Users by Role</h3>
            <table class="table" style="margin-top:0;">
                <thead><tr><th>Role</th><th>Count</th></tr></thead>
                <tbody>
                    <?php foreach ($roleRows as $row): ?>
                        <tr>
                            <td><span class="badge badge-<?= e($row['role']) ?>"><?= ucfirst(e($row['role'])) ?></span></td>
                            <td><strong><?= (int)$row['cnt'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Restaurants -->
    <div class="card mb-4" style="padding:24px;">
        <h3 class="mb-3">Top Restaurants by Orders</h3>
        <div class="table-wrap">
            <table class="table" style="margin-top:0;">
                <thead><tr><th>Rank</th><th>Restaurant</th><th>Total Orders</th><th>Total Revenue</th></tr></thead>
                <tbody>
                    <?php foreach ($topRestaurants as $i => $r): ?>
                        <tr>
                            <td><strong>#<?= $i + 1 ?></strong></td>
                            <td><?= e($r['name']) ?></td>
                            <td><?= (int)$r['order_count'] ?></td>
                            <td>RM <?= number_format($r['revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topRestaurants)): ?>
                        <tr><td colspan="4" class="text-muted">No data yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card" style="padding:24px;">
        <h3 class="mb-3">Recent Orders (Last 10)</h3>
        <div class="table-wrap">
            <table class="table" style="margin-top:0;">
                <thead><tr><th>#</th><th>Customer</th><th>Restaurant</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= e($o['cname']) ?></td>
                            <td><?= e($o['rname']) ?></td>
                            <td>RM <?= number_format($o['total_amount'], 2) ?></td>
                            <td><?= ucfirst(e($o['payment_method'])) ?></td>
                            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="7" class="text-muted">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
