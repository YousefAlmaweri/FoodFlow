<?php
$depth = substr_count(str_replace('\\','/',$_SERVER['PHP_SELF']), '/') - 2;
$base  = str_repeat('../', max(0, $depth));
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div>
                <span class="logo">FoodFlow</span>
                <p class="text-muted" style="margin-top:6px;font-size:0.88rem;">Craving Something? We Deliver.</p>
            </div>
            <nav class="footer-links">
                <a href="<?= $base ?>index.php">Restaurants</a>
                <a href="<?= $base ?>about.php">About</a>
                <a href="<?= $base ?>team.php">Our Team</a>
                <a href="<?= $base ?>auth.php">Login / Register</a>
            </nav>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 FoodFlow &mdash; Group 19 | CIT6224 Web Application Development | Multimedia University</p>
        </div>
    </div>
</footer>

<script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
