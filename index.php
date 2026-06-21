<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$search  = trim($_GET['search']  ?? '');
$cuisine = trim($_GET['cuisine'] ?? '');

$sql    = 'SELECT * FROM `restaurants` WHERE 1=1';
$params = [];

if ($search) {
    $sql     .= ' AND (`name` LIKE ? OR `cuisine` LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($cuisine && $cuisine !== 'All') {
    $sql     .= ' AND `cuisine` = ?';
    $params[] = $cuisine;
}
$sql .= ' ORDER BY `rating` DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

$cuisines = [
    'All', 'Local Cuisine', 'Western & Fast Food',
    'Healthy & Diet Meals', 'Beverages & Desserts', 'Snacks & Groceries'
];
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Craving Something? <span>We Deliver.</span></h1>
        <p>Discover the best local restaurants and get your favourite meals delivered fast.</p>

        <form method="GET" action="index.php" class="search-bar" role="search">
            <?php if ($cuisine && $cuisine !== 'All'): ?>
                <input type="hidden" name="cuisine" value="<?= e($cuisine) ?>">
            <?php endif; ?>
            <input type="text" name="search" value="<?= e($search) ?>"
                   class="form-control" placeholder="Search restaurants or cuisines..."
                   aria-label="Search restaurants">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>
</section>

<!-- Category Pills -->
<div class="container">
    <nav class="category-nav" aria-label="Filter by cuisine type">
        <?php foreach ($cuisines as $c): ?>
            <?php
            $active = ($c === 'All' && $cuisine === '') || $c === $cuisine;
            $href   = 'index.php?cuisine=' . urlencode($c) . ($search ? '&search=' . urlencode($search) : '');
            ?>
            <a href="<?= $href ?>" class="pill <?= $active ? 'active' : '' ?>">
                <?= e($c) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Restaurant Grid -->
<section class="container section">

    <div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
        <h2>
            <?php if ($cuisine && $cuisine !== 'All'): ?>
                <?= e($cuisine) ?>
            <?php elseif ($search): ?>
                Results for &ldquo;<?= e($search) ?>&rdquo;
            <?php else: ?>
                Featured Restaurants
            <?php endif; ?>
            <span class="text-muted fs-sm fw-600">(<?= count($restaurants) ?>)</span>
        </h2>
        <?php if ($search || ($cuisine && $cuisine !== 'All')): ?>
            <a href="index.php" class="btn btn-outline btn-sm">Clear Filter</a>
        <?php endif; ?>
    </div>

    <?php if (empty($restaurants)): ?>
        <div class="card" style="padding:48px;text-align:center;">
            <p class="text-muted" style="font-size:1.05rem;margin-bottom:20px;">No restaurants found. Try a different search.</p>
            <a href="index.php" class="btn">View All Restaurants</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($restaurants as $r): ?>
                <a href="restaurant.php?id=<?= (int)$r['id'] ?>" class="card" style="color:inherit;">
                    <img src="<?= e($r['image']) ?>"
                         alt="<?= e($r['name']) ?>"
                         class="card-img"
                         onerror="this.src='https://picsum.photos/seed/<?= (int)$r['id'] ?>/600/300'">
                    <div class="card-body">
                        <h3 class="card-title"><?= e($r['name']) ?></h3>
                        <div class="card-meta">
                            <span>Rating: <?= number_format($r["rating"], 1) ?></span>
                            <span><?= e($r['cuisine']) ?></span>
                            <span><?= (int)$r["est_delivery_time"] ?> min</span>
                        </div>
                        <div class="d-flex justify-between align-center">
                            <span class="text-muted fs-sm">Min. RM <?= number_format($r['min_order'], 2) ?></span>
                            <span class="btn btn-sm" style="pointer-events:none;">Order Now</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php require_once 'includes/footer.php'; ?>
