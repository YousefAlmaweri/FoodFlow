<?php
session_start();

$db_name = 'foodflow_db';
$db_user = 'root';
$db_pass = '';

$pdo = null;
foreach ([['localhost','3306'],['localhost','3307'],['127.0.0.1','3306'],['127.0.0.1','3307']] as [$h,$p]) {
    try {
        $pdo = new PDO("mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 3,
        ]);
        break;
    } catch (\PDOException $e) { $pdo = null; }
}

if ($pdo === null) {
    die("<div style='font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;border:2px solid #e74c3c;border-radius:8px;color:#e74c3c;'>
        <h2>Database Connection Failed</h2>
        <p style='color:#333;'>Make sure MySQL is running in XAMPP, then visit <a href='setup_db.php'>setup_db.php</a> first.</p>
    </div>");
}

function isLoggedIn() { return isset($_SESSION['user_id']); }
function hasRole($r)   { return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $r; }
function e($s)         { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
