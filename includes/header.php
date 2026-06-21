<?php
require_once __DIR__ . '/../config.php';
$pageTitle = $pageTitle ?? 'FoodFlow';
$depth = substr_count(str_replace('\\','/',$_SERVER['PHP_SELF']), '/') - 2;
$base  = str_repeat('../', max(0, $depth));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - FoodFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $base ?>index.php" class="logo">FoodFlow</a>

        <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">&#9776;</button>

        <nav class="nav-links" id="mainNav">
            <a href="<?= $base ?>index.php">Restaurants</a>
            <a href="<?= $base ?>team.php">Our Team</a>
            <a href="<?= $base ?>about.php">About</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= $base ?>profile.php">Profile</a>
                <a href="<?= $base ?>dashboard.php">Dashboard</a>
                <?php if (hasRole('customer')): ?>
                    <a href="<?= $base ?>orders.php">My Orders</a>
                    <a href="<?= $base ?>checkout.php" class="cart-link">Cart <span class="cart-badge" id="cart-count">0</span></a>
                <?php endif; ?>
                <?php if (hasRole('partner')): ?>
                    <a href="<?= $base ?>menu.php">Manage Menu</a>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                    <a href="<?= $base ?>reports.php">Reports</a>
                <?php endif; ?>
                <a href="<?= $base ?>auth.php?action=logout" class="btn btn-outline btn-sm">Logout</a>
            <?php else: ?>
                <a href="<?= $base ?>checkout.php" class="cart-link">Cart <span class="cart-badge" id="cart-count">0</span></a>
                <a href="<?= $base ?>auth.php" class="btn btn-sm">Login / Register</a>
            <?php endif; ?>
            <button onclick="toggleTheme()" class="btn btn-outline btn-sm" title="Toggle theme">Theme</button>
        </nav>
    </div>
</header>

<main>
