<?php
$pageTitle = 'About FoodFlow';
require_once 'includes/header.php';
?>

<section class="hero" style="padding:60px 0;">
    <div class="container">
        <h1>About <span style="color:var(--primary);">FoodFlow</span></h1>
        <p>A modern food delivery platform built for CIT6224 Web Application Development.</p>
    </div>
</section>

<div class="container section" style="max-width:860px;">

    <!-- What is FoodFlow -->
    <div class="card" style="padding:32px;margin-bottom:28px;">
        <h2 class="mb-3">What is FoodFlow?</h2>
        <p style="color:var(--text-muted);line-height:1.8;">
            FoodFlow is a Business-to-Consumer (B2C) food delivery web application that allows customers
            to browse local restaurants, add meals to their cart, and place orders for delivery.
            Restaurant partners manage their menus and incoming orders through a dedicated dashboard,
            while administrators oversee the entire platform.
        </p>
        <p style="color:var(--text-muted);line-height:1.8;margin-top:14px;">
            The platform focuses on providing a seamless, secure, and intuitive ordering experience
            for customers while simplifying operations for restaurant partners.
        </p>
    </div>

    <!-- Key Features -->
    <div class="card" style="padding:32px;margin-bottom:28px;">
        <h2 class="mb-3">Key Features</h2>
        <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;padding:0;">

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Restaurant Search</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Browse and filter restaurants by cuisine type or keyword search.</p>
            </div>

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Shopping Cart</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Add items, adjust quantities, and review your order before checkout.</p>
            </div>

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Secure Login</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Role-based accounts for customers, restaurant partners, and admins.</p>
            </div>

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Order Tracking</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Track your order status from placement through to delivery.</p>
            </div>

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Partner Dashboard</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Restaurant partners manage menus, stock, and incoming orders.</p>
            </div>

            <div style="background:var(--bg-input);border-radius:8px;padding:18px;">
                <h4 style="color:var(--primary);margin-bottom:8px;">Admin Panel</h4>
                <p style="color:var(--text-muted);font-size:0.88rem;">Administrators manage users, restaurants, and platform orders.</p>
            </div>

        </div>
    </div>

    <!-- Tech Stack -->
    <div class="card" style="padding:32px;margin-bottom:28px;">
        <h2 class="mb-3">Technology Stack</h2>
        <table class="table" style="margin-top:0;">
            <thead>
                <tr><th>Layer</th><th>Technology</th></tr>
            </thead>
            <tbody>
                <tr><td>Frontend</td><td>HTML5, CSS3 (custom — no frameworks), JavaScript (Vanilla)</td></tr>
                <tr><td>Backend</td><td>PHP (server-side logic, sessions, validation)</td></tr>
                <tr><td>Database</td><td>MySQL with PDO prepared statements</td></tr>
                <tr><td>Server</td><td>XAMPP (Apache + MySQL) on Windows</td></tr>
                <tr><td>Security</td><td>PDO prepared statements, htmlspecialchars(), bcrypt password hashing, session management</td></tr>
                <tr><td>Version Control</td><td>Git and GitHub</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Security -->
    <div class="card" style="padding:32px;margin-bottom:28px;">
        <h2 class="mb-3">Security Measures</h2>
        <table class="table" style="margin-top:0;">
            <thead>
                <tr><th>Threat</th><th>Protection</th></tr>
            </thead>
            <tbody>
                <tr><td>SQL Injection</td><td>All database queries use PDO prepared statements with parameterised values.</td></tr>
                <tr><td>Cross-Site Scripting (XSS)</td><td>All output is escaped with <code>htmlspecialchars()</code> before rendering.</td></tr>
                <tr><td>Session Fixation</td><td><code>session_regenerate_id(true)</code> is called on every successful login.</td></tr>
                <tr><td>Password Storage</td><td>Passwords are hashed using <code>password_hash(PASSWORD_DEFAULT)</code> (bcrypt).</td></tr>
                <tr><td>Unauthorised Access</td><td>Role-based access control redirects unauthenticated or unauthorised users.</td></tr>
                <tr><td>Price Tampering</td><td>Cart prices are re-verified from the database at checkout — client values are never trusted.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <a href="index.php" class="btn btn-lg">Browse Restaurants</a>
        <a href="team.php" class="btn btn-outline btn-lg" style="margin-left:12px;">Meet the Team</a>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
