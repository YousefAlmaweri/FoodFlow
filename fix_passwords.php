<?php
/**
 * FoodFlow — Emergency Password Reset
 * Run ONCE at: http://localhost/assignment/fix_passwords.php
 * DELETE THIS FILE immediately after use.
 */

// Try all host/port combos
$candidates = [
    ['localhost', '3306'],
    ['localhost', '3307'],
    ['127.0.0.1', '3306'],
    ['127.0.0.1', '3307'],
];

$pdo = null;
foreach ($candidates as [$h, $p]) {
    try {
        $pdo = new PDO(
            "mysql:host=$h;port=$p;dbname=foodflow_db;charset=utf8mb4",
            'root', '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
        );
        break;
    } catch (\PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodFlow — Fix Passwords</title>
<style>
  body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
  .ok  { color: #27ae60; font-weight: 600; }
  .err { color: #e74c3c; font-weight: 600; }
  pre  { background: #f4f4f4; padding: 16px; border-radius: 8px; }
  a    { color: #E67E22; font-weight: 600; }
  .box { background: #fff8f0; border: 2px solid #E67E22; border-radius: 8px; padding: 20px; margin: 20px 0; }
</style>
</head>
<body>
<h1>🔑 FoodFlow — Password Fix</h1>

<?php if (!$pdo): ?>
    <p class="err">❌ Could not connect to database. Make sure MySQL is running and setup_db.php was completed first.</p>
<?php else: ?>

<?php
// Delete all existing users and re-insert with correct hashes
$pdo->exec("DELETE FROM order_items");
$pdo->exec("DELETE FROM orders");
$pdo->exec("DELETE FROM menu_items");
$pdo->exec("DELETE FROM restaurants");
$pdo->exec("DELETE FROM users");
$pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE restaurants AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE menu_items AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE orders AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE order_items AUTO_INCREMENT = 1");

// Insert users with properly hashed passwords
$ins = $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)');
$users = [
    ['Admin FoodFlow',        'admin@foodflow.com',       '0123456789', password_hash('Admin123!',    PASSWORD_DEFAULT), 'admin'],
    ['Partner Burger Hub',    'partner@burgerhub.com',    '0129876543', password_hash('Partner123!',  PASSWORD_DEFAULT), 'partner'],
    ['Partner Asian Kitchen', 'partner@asiankitchen.com', '0111234567', password_hash('Partner123!',  PASSWORD_DEFAULT), 'partner'],
    ['Test Customer',         'customer@foodflow.com',    '0134567890', password_hash('Customer123!', PASSWORD_DEFAULT), 'customer'],
];
foreach ($users as $u) {
    $ins->execute($u);
    echo '<p class="ok">✅ User <strong>' . htmlspecialchars($u[1]) . '</strong> reset.</p>';
}

// Re-insert restaurants
$p1 = $pdo->query("SELECT id FROM users WHERE email='partner@burgerhub.com'")->fetchColumn();
$p2 = $pdo->query("SELECT id FROM users WHERE email='partner@asiankitchen.com'")->fetchColumn();

$insR = $pdo->prepare('INSERT INTO restaurants (partner_id,name,image,cuisine,rating,min_order,est_delivery_time) VALUES (?,?,?,?,?,?,?)');
$insR->execute([$p1,'Burger Hub',   'https://picsum.photos/seed/burgerhub/800/400',   'Western & Fast Food',4.8,12.00,25]);
$insR->execute([$p2,'Asian Kitchen','https://picsum.photos/seed/asiankitchen/800/400','Local Cuisine',      4.7,10.00,30]);

$r1 = $pdo->query("SELECT id FROM restaurants WHERE name='Burger Hub'")->fetchColumn();
$r2 = $pdo->query("SELECT id FROM restaurants WHERE name='Asian Kitchen'")->fetchColumn();

$insM = $pdo->prepare('INSERT INTO menu_items (restaurant_id,name,description,price,image,is_available) VALUES (?,?,?,?,?,?)');
$items = [
    [$r1,'Classic Cheeseburger','Beef patty with cheddar, lettuce, tomato and pickles.',     14.90,'https://picsum.photos/seed/cheeseburger/400/300',    1],
    [$r1,'BBQ Bacon Burger',    'Double beef patty with crispy bacon and BBQ sauce.',         18.90,'https://picsum.photos/seed/bbqburger/400/300',       1],
    [$r1,'Chicken Sandwich',    'Grilled chicken fillet with lettuce and honey mustard.',     13.90,'https://picsum.photos/seed/chickensandwich/400/300', 1],
    [$r1,'Loaded Fries',        'Crispy fries topped with cheese sauce and jalapeños.',        8.90,'https://picsum.photos/seed/loadedfries/400/300',    1],
    [$r1,'Chocolate Milkshake', 'Thick creamy chocolate milkshake.',                           7.90,'https://picsum.photos/seed/milkshake/400/300',      1],
    [$r2,'Nasi Lemak Special',  'Fragrant coconut rice with sambal, anchovies, egg, peanuts.',12.00,'https://picsum.photos/seed/nasilemak/400/300',     1],
    [$r2,'Char Kuey Teow',      'Stir-fried flat rice noodles with prawns and bean sprouts.',13.50,'https://picsum.photos/seed/charkt/400/300',         1],
    [$r2,'Laksa Lemak',         'Rich coconut curry noodle soup with prawns.',                14.00,'https://picsum.photos/seed/laksa/400/300',          1],
    [$r2,'Teh Tarik',           'Pulled milk tea, Malaysian style.',                           3.50,'https://picsum.photos/seed/tehtarik/400/300',      1],
];
foreach ($items as $m) $insM->execute($m);
echo '<p class="ok">✅ Restaurants and menu items re-seeded.</p>';
?>

<hr>
<h2>✅ All Done!</h2>
<div class="box">
<strong>Login Credentials (now working):</strong>
<pre>
👤 Admin
   Email:    admin@foodflow.com
   Password: Admin123!

🍔 Partner (Burger Hub)
   Email:    partner@burgerhub.com
   Password: Partner123!

🍜 Partner (Asian Kitchen)
   Email:    partner@asiankitchen.com
   Password: Partner123!

🛒 Customer
   Email:    customer@foodflow.com
   Password: Customer123!
</pre>
</div>

<p><a href="auth.php">▶ Go to Login Page →</a></p>
<p style="color:#e74c3c; font-size:0.9rem;">⚠️ Delete <code>fix_passwords.php</code> from your project folder after logging in successfully.</p>

<?php endif; ?>
</body>
</html>
