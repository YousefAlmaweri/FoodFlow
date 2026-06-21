<?php
$pageTitle = 'Our Team';
require_once 'includes/header.php';
?>

<section class="hero" style="padding:60px 0;">
    <div class="container">
        <h1>Meet the Team</h1>
        <p>Group 19 &mdash; CIT6224 Web Application Development &mdash; Multimedia University</p>
    </div>
</section>

<div class="container section">

    <div class="team-grid">

        <!-- Member 1 -->
        <div class="team-card">
            <div class="team-avatar">YM</div>
            <h3>Al-Maweri Yousef Mohammed Abdullah</h3>
            <div class="team-student-id">Student ID: 241UC240P4</div>
            <div class="team-role">Lead Full-Stack Developer</div>
            <ul class="team-pages">
                <li>Home Page (index.php)</li>
                <li>Restaurant &amp; Menu (restaurant.php)</li>
                <li>Checkout (checkout.php)</li>
                <li>About Page (about.php)</li>
            </ul>
            <div class="mt-3 text-muted fs-sm" style="text-align:left;">
                <strong style="color:var(--text);">Features:</strong>
                Restaurant search and category filtering, dynamic JavaScript cart with real-time
                total updates, checkout order processing with server-side price verification,
                client-side form validation.
            </div>
        </div>

        <!-- Member 2 -->
        <div class="team-card">
            <div class="team-avatar">BS</div>
            <h3>Bara Samih Jamal Yousef</h3>
            <div class="team-student-id">Student ID: 241UC2400T</div>
            <div class="team-role">Lead Systems &amp; Security Developer</div>
            <ul class="team-pages">
                <li>Login &amp; Registration (auth.php)</li>
                <li>Dashboard (dashboard.php)</li>
                <li>User Profile (profile.php)</li>
                <li>Team Page (team.php)</li>
            </ul>
            <div class="mt-3 text-muted fs-sm" style="text-align:left;">
                <strong style="color:var(--text);">Features:</strong>
                Session-based authentication with session regeneration, role-based access control,
                PHP server-side validation on all forms, database schema and ERD design,
                SQL injection prevention via PDO, XSS prevention via htmlspecialchars.
            </div>
        </div>

        <!-- Member 3 -->
        <div class="team-card">
            <div class="team-avatar">AB</div>
            <h3>Abdulmalik Babiker Fadlalmula Hussain</h3>
            <div class="team-student-id">Student ID: 241UC240T4</div>
            <div class="team-role">Backend &amp; Operations Developer</div>
            <ul class="team-pages">
                <li>Order History (orders.php)</li>
                <li>Partner Menu Management (menu.php)</li>
                <li>Admin Analytics &amp; Reports (reports.php)</li>
            </ul>
            <div class="mt-3 text-muted fs-sm" style="text-align:left;">
                <strong style="color:var(--text);">Features:</strong>
                Customer order history with itemised breakdown and reorder button,
                partner menu CRUD with availability toggle,
                admin analytics dashboard with order statistics, revenue reports,
                top restaurants table and user role breakdown.
            </div>
        </div>

    </div>

    <!-- Project Details -->
    <div class="card mt-4" style="max-width:720px;margin:0 auto;padding:30px;">
        <h2 class="mb-4 text-center">Project Details</h2>
        <table class="table" style="margin-top:0;">
            <tbody>
                <tr><td class="text-muted" style="width:160px;">Project</td>       <td><strong>FoodFlow</strong></td></tr>
                <tr><td class="text-muted">Course</td>       <td>CIT6224 &mdash; Web Application Development</td></tr>
                <tr><td class="text-muted">Group</td>        <td>Group 19</td></tr>
                <tr><td class="text-muted">Institution</td>  <td>Multimedia University (MMU), Cyberjaya</td></tr>
                <tr><td class="text-muted">Tech Stack</td>   <td>HTML5 &middot; CSS3 &middot; JavaScript &middot; PHP &middot; MySQL &middot; XAMPP</td></tr>
                <tr><td class="text-muted">Methodology</td>  <td>Agile-inspired incremental development</td></tr>
                <tr><td class="text-muted">Version Control</td><td>Git &amp; GitHub</td></tr>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
