<?php
require_once 'config.php';
if (!isLoggedIn()) { header('Location: auth.php'); exit; }

$uid    = (int)$_SESSION['user_id'];
$errors = [];
$msg    = '';

$s = $pdo->prepare('SELECT * FROM `users` WHERE `id`=? LIMIT 1');
$s->execute([$uid]); $user = $s->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']             ?? '');
    $phone   = trim($_POST['phone']            ?? '');
    $newpass =      $_POST['new_password']     ?? '';
    $confirm =      $_POST['confirm_password'] ?? '';

    if (!$name || mb_strlen($name) < 2) $errors['name']  = 'Full name must be at least 2 characters.';
    if ($phone !== '' && !preg_match('/^[\d\s\-\+\(\)]{7,15}$/', $phone)) $errors['phone'] = 'Phone must be 7–15 digits.';

    if ($newpass !== '') {
        if (strlen($newpass) < 8)              $errors['pass']    = 'Password must be at least 8 characters.';
        elseif (!preg_match('/[A-Za-z]/', $newpass)) $errors['pass'] = 'Password must contain a letter.';
        elseif (!preg_match('/[0-9]/',    $newpass)) $errors['pass'] = 'Password must contain a number.';
        elseif ($newpass !== $confirm)         $errors['confirm'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if ($newpass) {
            $pdo->prepare('UPDATE `users` SET `name`=?,`phone`=?,`password`=? WHERE `id`=?')
                ->execute([$name, $phone, password_hash($newpass, PASSWORD_DEFAULT), $uid]);
        } else {
            $pdo->prepare('UPDATE `users` SET `name`=?,`phone`=? WHERE `id`=?')
                ->execute([$name, $phone, $uid]);
        }
        $_SESSION['user_name'] = $name;
        $msg = 'Profile updated successfully.';
        $s->execute([$uid]); $user = $s->fetch();
    }
}

$pageTitle = 'My Profile';
require_once 'includes/header.php';
?>

<div class="container py-6" style="max-width:640px;">
    <h1 class="mb-4">My Profile</h1>

    <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

    <div class="card" style="padding:30px;">

        <!-- Read-only info -->
        <div class="mb-4" style="padding-bottom:20px;border-bottom:1px solid var(--border);">
            <div class="profile-field"><span class="profile-label">Email</span><span><?= e($user['email']) ?></span></div>
            <div class="profile-field"><span class="profile-label">Role</span><span><span class="badge badge-<?= e($user['role']) ?>"><?= ucfirst($user['role']) ?></span></span></div>
            <div class="profile-field"><span class="profile-label">Member Since</span><span><?= date('d M Y', strtotime($user['created_at'])) ?></span></div>
        </div>

        <form method="POST" action="profile.php" id="profile-form" novalidate>

            <div class="form-group">
                <label for="p_name">Full Name <span class="required-star">*</span></label>
                <input type="text" id="p_name" name="name"
                       class="form-control <?= !empty($errors['name']) ? 'is-error' : '' ?>"
                       value="<?= e($_POST['name'] ?? $user['name']) ?>"
                       required autocomplete="name">
                <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="p_phone">Phone Number</label>
                <input type="tel" id="p_phone" name="phone"
                       class="form-control <?= !empty($errors['phone']) ? 'is-error' : '' ?>"
                       value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>"
                       placeholder="e.g. 0123456789" autocomplete="tel">
                <?php if (!empty($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
            </div>

            <hr style="border-color:var(--border);margin:20px 0;">
            <p class="text-muted fs-sm mb-3">Leave password fields blank to keep your current password.</p>

            <div class="form-group">
                <label for="p_newpass">New Password</label>
                <input type="password" id="p_newpass" name="new_password"
                       class="form-control <?= !empty($errors['pass']) ? 'is-error' : '' ?>"
                       placeholder="Min 8 chars, letters &amp; numbers" autocomplete="new-password">
                <?php if (!empty($errors['pass'])): ?><span class="field-error"><?= e($errors['pass']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="p_confirm">Confirm New Password</label>
                <input type="password" id="p_confirm" name="confirm_password"
                       class="form-control <?= !empty($errors['confirm']) ? 'is-error' : '' ?>"
                       placeholder="Re-enter new password" autocomplete="new-password">
                <?php if (!empty($errors['confirm'])): ?><span class="field-error"><?= e($errors['confirm']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-block">Save Changes</button>
        </form>
    </div>

    <div class="text-center mt-3">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
