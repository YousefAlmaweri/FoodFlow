<?php
require_once 'config.php';

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Redirect already-logged-in users
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors  = [];
$success = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================================================================
    // LOGIN
    // ================================================================
    if ($action === 'login') {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        // PHP server-side validation
        if (!$email) {
            $errors['login_email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['login_email'] = 'Please enter a valid email address.';
        }
        if (!$password) {
            $errors['login_pass'] = 'Password is required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT * FROM `users` WHERE `email` = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $errors['login_general'] = 'Incorrect email or password. Please try again.';
            }
        }
        $old['login_email'] = e($email);
    }

    // ================================================================
    // REGISTER
    // ================================================================
    elseif ($action === 'register') {
        $name     = trim($_POST['name']             ?? '');
        $email    = trim($_POST['email']            ?? '');
        $phone    = trim($_POST['phone']            ?? '');
        $password =      $_POST['password']         ?? '';
        $confirm  =      $_POST['password_confirm'] ?? '';
        $role     =      $_POST['role']             ?? 'customer';

        // Whitelist role
        $role = in_array($role, ['customer', 'partner'], true) ? $role : 'customer';

        //  PHP server-side validation (rubric requirement) 

        // Full name
        if (!$name) {
            $errors['name'] = 'Full name is required.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'Full name must be at least 2 characters.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Full name must not exceed 100 characters.';
        }

        // Email
        if (!$email) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address (e.g. user@example.com).';
        } elseif (mb_strlen($email) > 100) {
            $errors['email'] = 'Email address must not exceed 100 characters.';
        } else {
            // Check duplicate email
            $chk = $pdo->prepare('SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors['email'] = 'This email is already registered. Please login instead.';
            }
        }

        // Phone (optional)
        if ($phone !== '' && !preg_match('/^[\d\s\-\+\(\)]{7,15}$/', $phone)) {
            $errors['phone'] = 'Phone number must be 7–15 digits (spaces and dashes allowed).';
        }

        // Password strength
        if (!$password) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Za-z]/', $password)) {
            $errors['password'] = 'Password must contain at least one letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least one number.';
        }

        // Confirm password
        if (!$confirm) {
            $errors['confirm'] = 'Please confirm your password.';
        } elseif ($confirm !== $password) {
            $errors['confirm'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'INSERT INTO `users` (`name`,`email`,`phone`,`password`,`role`) VALUES (?,?,?,?,?)'
            );
            $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role]);
            $success = 'Account created! You can now log in below.';
        }

        $old = ['name' => e($name), 'email' => e($email), 'phone' => e($phone), 'role' => $role];
    }
}

$pageTitle = 'Login / Register';
require_once 'includes/header.php';
?>

<div class="container py-6" style="max-width:940px;">

    <?php if (!empty($errors['login_general'])): ?>
        <div class="alert alert-error"><?= e($errors['login_general']) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="grid-2">

        <!--  LOGIN  -->
        <div class="card" style="padding:32px;">
            <h2 class="mb-1">Welcome Back</h2>
            <p class="text-muted fs-sm mb-4">Sign in to your FoodFlow account.</p>

            <form method="POST" action="auth.php" id="login-form" novalidate>
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="l_email">Email Address</label>
                    <input type="email" id="l_email" name="email"
                           class="form-control <?= !empty($errors['login_email']) ? 'is-error' : '' ?>"
                           value="<?= $old['login_email'] ?? '' ?>"
                           placeholder="you@example.com"
                           autocomplete="email" required>
                    <?php if (!empty($errors['login_email'])): ?>
                        <span class="field-error"><?= e($errors['login_email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="l_pass">Password</label>
                    <input type="password" id="l_pass" name="password"
                           class="form-control <?= !empty($errors['login_pass']) ? 'is-error' : '' ?>"
                           placeholder="Your password"
                           autocomplete="current-password" required>
                    <?php if (!empty($errors['login_pass'])): ?>
                        <span class="field-error"><?= e($errors['login_pass']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-block mt-2">Login</button>
            </form>
        </div>

        <!--  REGISTER  -->
        <div class="card" style="padding:32px;">
            <h2 class="mb-1">Create Account</h2>
            <p class="text-muted fs-sm mb-4">Join FoodFlow — it's free!</p>

            <form method="POST" action="auth.php" id="register-form" novalidate>
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label for="r_name">Full Name <span class="required-star">*</span></label>
                    <input type="text" id="r_name" name="name"
                           class="form-control <?= !empty($errors['name']) ? 'is-error' : '' ?>"
                           value="<?= $old['name'] ?? '' ?>"
                           placeholder="e.g. Ahmad bin Ali"
                           autocomplete="name" required>
                    <?php if (!empty($errors['name'])): ?>
                        <span class="field-error"><?= e($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="r_email">Email Address <span class="required-star">*</span></label>
                    <input type="email" id="r_email" name="email"
                           class="form-control <?= !empty($errors['email']) ? 'is-error' : '' ?>"
                           value="<?= $old['email'] ?? '' ?>"
                           placeholder="you@example.com"
                           autocomplete="email" required>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="field-error"><?= e($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="r_phone">Phone Number</label>
                    <input type="tel" id="r_phone" name="phone"
                           class="form-control <?= !empty($errors['phone']) ? 'is-error' : '' ?>"
                           value="<?= $old['phone'] ?? '' ?>"
                           placeholder="e.g. 0123456789"
                           autocomplete="tel">
                    <?php if (!empty($errors['phone'])): ?>
                        <span class="field-error"><?= e($errors['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="r_pass">Password <span class="required-star">*</span></label>
                    <input type="password" id="r_pass" name="password"
                           class="form-control <?= !empty($errors['password']) ? 'is-error' : '' ?>"
                           placeholder="Min 8 chars, letters &amp; numbers"
                           autocomplete="new-password" required>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="field-error"><?= e($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="r_confirm">Confirm Password <span class="required-star">*</span></label>
                    <input type="password" id="r_confirm" name="password_confirm"
                           class="form-control <?= !empty($errors['confirm']) ? 'is-error' : '' ?>"
                           placeholder="Re-enter your password"
                           autocomplete="new-password" required>
                    <?php if (!empty($errors['confirm'])): ?>
                        <span class="field-error"><?= e($errors['confirm']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="r_role">Register as:</label>
                    <select id="r_role" name="role" class="form-control">
                        <option value="customer" <?= (($old['role'] ?? '') === 'customer' || !isset($old['role'])) ? 'selected' : '' ?>>Customer — Order Food</option>
                        <option value="partner"  <?= (($old['role'] ?? '') === 'partner')  ? 'selected' : '' ?>>Restaurant Partner — Sell Food</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline btn-block mt-2">Create Account</button>
            </form>
        </div>

    </div><!-- /grid-2 -->
</div>

<?php require_once 'includes/footer.php'; ?>
