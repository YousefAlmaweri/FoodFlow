<?php
/**
 * FoodFlow - Database Setup
 * Visit once: http://localhost/assignment/setup_db.php
 */
$db_name = 'foodflow_db';
$db_user = 'root';
$db_pass = '';

$pdo = null; $conn = '';
foreach ([['localhost','3306'],['localhost','3307'],['127.0.0.1','3306'],['127.0.0.1','3307']] as [$h,$p]) {
    try {
        $pdo = new PDO("mysql:host=$h;port=$p;charset=utf8mb4", $db_user, $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
        $conn = "$h:$p"; break;
    } catch (\PDOException $e) { $pdo = null; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodFlow - Setup</title>
<style>
body  { font-family:sans-serif; max-width:680px; margin:50px auto; padding:0 20px; }
h1    { color:#E67E22; }
.ok   { color:#27ae60; font-weight:600; }
.err  { color:#e74c3c; font-weight:600; }
.warn { color:#e67e22; font-weight:600; }
pre   { background:#f0f0f0; padding:16px; border-radius:8px; }
.box  { background:#fff8f0; border:2px solid #E67E22; border-radius:8px; padding:20px; margin:20px 0; }
a     { color:#E67E22; }
</style>
</head>
<body>
<h1>FoodFlow - Database Setup</h1>

<?php if (!$pdo): ?>
<p class="err">Could not connect to MySQL. Tried localhost:3306, localhost:3307, 127.0.0.1:3306, 127.0.0.1:3307.</p>
<h3>Fix steps:</h3>
<ol>
  <li>Open XAMPP Control Panel and start MySQL.</li>
  <li>If MySQL crashes: delete <code>ib_logfile0</code> and <code>ib_logfile1</code> from <code>C:\xampp\mysql\data\</code> then retry.</li>
  <li>If root has a password, edit <code>$db_pass</code> at the top of this file.</li>
</ol>
<pre>PHP version : <?= PHP_VERSION ?>

PDO drivers : <?= implode(', ', PDO::getAvailableDrivers()) ?></pre>
<?php else: ?>

<p class="ok">Connected to MySQL at <?= $conn ?></p>

<?php
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(100) NOT NULL,
        `email`      VARCHAR(100) NOT NULL UNIQUE,
        `phone`      VARCHAR(20)  DEFAULT NULL,
        `password`   VARCHAR(255) NOT NULL,
        `role`       ENUM('customer','partner','admin','rider') NOT NULL DEFAULT 'customer',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo '<p class="ok">Table users ready.</p>';

    $pdo->exec("CREATE TABLE IF NOT EXISTS `restaurants` (
        `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `partner_id`        INT UNSIGNED NOT NULL,
        `name`              VARCHAR(150) NOT NULL,
        `image`             TEXT DEFAULT NULL,
        `cuisine`           VARCHAR(80)  NOT NULL,
        `rating`            DECIMAL(3,1) NOT NULL DEFAULT 0.0,
        `min_order`         DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `est_delivery_time` INT NOT NULL DEFAULT 30,
        `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`partner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo '<p class="ok">Table restaurants ready.</p>';

    $pdo->exec("CREATE TABLE IF NOT EXISTS `menu_items` (
        `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `restaurant_id` INT UNSIGNED NOT NULL,
        `name`          VARCHAR(150) NOT NULL,
        `description`   TEXT DEFAULT NULL,
        `price`         DECIMAL(8,2) NOT NULL,
        `image`         TEXT DEFAULT NULL,
        `is_available`  TINYINT(1) NOT NULL DEFAULT 1,
        `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo '<p class="ok">Table menu_items ready.</p>';

    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `customer_id`      INT UNSIGNED NOT NULL,
        `restaurant_id`    INT UNSIGNED NOT NULL,
        `total_amount`     DECIMAL(10,2) NOT NULL,
        `delivery_fee`     DECIMAL(8,2) NOT NULL DEFAULT 5.00,
        `delivery_address` TEXT NOT NULL,
        `payment_method`   ENUM('cash','card') NOT NULL DEFAULT 'cash',
        `status`           ENUM('pending','accepted','rejected','picking_up','delivering','delivered') NOT NULL DEFAULT 'pending',
        `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`customer_id`)   REFERENCES `users`(`id`)       ON DELETE CASCADE,
        FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo '<p class="ok">Table orders ready.</p>';

    $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (
        `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `order_id`     INT UNSIGNED NOT NULL,
        `menu_item_id` INT UNSIGNED NOT NULL,
        `quantity`     INT UNSIGNED NOT NULL DEFAULT 1,
        `unit_price`   DECIMAL(8,2) NOT NULL,
        `subtotal`     DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (`order_id`)     REFERENCES `orders`(`id`)     ON DELETE CASCADE,
        FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo '<p class="ok">Table order_items ready.</p>';

    // Seed only if empty
    if ((int)$pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn() === 0) {

        $iu = $pdo->prepare('INSERT INTO `users` (`name`,`email`,`phone`,`password`,`role`) VALUES (?,?,?,?,?)');
        $users = [
            ['Admin FoodFlow',        'admin@foodflow.com',       '0123456789', 'Admin123!',    'admin'],
            ['Burger Hub Partner',    'partner@burgerhub.com',    '0129876543', 'Partner123!',  'partner'],
            ['Asian Kitchen Partner', 'partner@asiankitchen.com', '0111234567', 'Partner123!',  'partner'],
            ['Healthy Bowl Partner',  'partner@healthybowl.com',  '0112345678', 'Partner123!',  'partner'],
            ['Dessert House Partner', 'partner@desserthouse.com', '0113456789', 'Partner123!',  'partner'],
            ['Mamak Corner Partner',  'partner@mamakcorner.com',  '0114567890', 'Partner123!',  'partner'],
            ['Test Customer',         'customer@foodflow.com',    '0134567890', 'Customer123!', 'customer'],
        ];
        foreach ($users as $u) {
            $iu->execute([$u[0],$u[1],$u[2],password_hash($u[3],PASSWORD_DEFAULT),$u[4]]);
        }

        // Get partner IDs
        $pids = [];
        foreach (['partner@burgerhub.com','partner@asiankitchen.com','partner@healthybowl.com','partner@desserthouse.com','partner@mamakcorner.com'] as $em) {
            $pids[] = (int)$pdo->query("SELECT `id` FROM `users` WHERE `email`='$em'")->fetchColumn();
        }

        // 5 restaurants with real Unsplash images (no emoji)
        $ir = $pdo->prepare('INSERT INTO `restaurants` (`partner_id`,`name`,`image`,`cuisine`,`rating`,`min_order`,`est_delivery_time`) VALUES (?,?,?,?,?,?,?)');
        $restaurants = [
            [$pids[0], 'Burger Hub',     'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 'Western & Fast Food',  4.8, 12.00, 25],
            [$pids[1], 'Asian Kitchen',  'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80', 'Local Cuisine',        4.7, 10.00, 30],
            [$pids[2], 'Healthy Bowl',   'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', 'Healthy & Diet Meals', 4.6, 15.00, 35],
            [$pids[3], 'Dessert House',  'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=800&q=80', 'Beverages & Desserts', 4.9,  8.00, 20],
            [$pids[4], 'Mamak Corner',   'https://images.unsplash.com/photo-1596560548464-f010549b84d7?w=800&q=80', 'Local Cuisine',        4.5,  8.00, 20],
        ];
        foreach ($restaurants as $r) { $ir->execute($r); }

        // Get restaurant IDs
        $rids = [];
        foreach (['Burger Hub','Asian Kitchen','Healthy Bowl','Dessert House','Mamak Corner'] as $rn) {
            $rids[] = (int)$pdo->query("SELECT `id` FROM `restaurants` WHERE `name`='$rn'")->fetchColumn();
        }

        $im = $pdo->prepare('INSERT INTO `menu_items` (`restaurant_id`,`name`,`description`,`price`,`image`,`is_available`) VALUES (?,?,?,?,?,?)');

        // Burger Hub menu
        $burgerItems = [
            ['Classic Cheeseburger', 'Beef patty with cheddar, lettuce, tomato and pickles.',     14.90, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80'],
            ['BBQ Bacon Burger',     'Double beef patty with crispy bacon and BBQ sauce.',         18.90, 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&q=80'],
            ['Chicken Sandwich',     'Grilled chicken fillet with lettuce and honey mustard.',     13.90, 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=400&q=80'],
            ['Loaded Fries',         'Crispy fries topped with cheese sauce and jalapenos.',        8.90, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&q=80'],
            ['Chocolate Milkshake',  'Thick creamy chocolate milkshake.',                           7.90, 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400&q=80'],
        ];
        foreach ($burgerItems as $i) { $im->execute([$rids[0],$i[0],$i[1],$i[2],$i[3],1]); }

        // Asian Kitchen menu
        $asianItems = [
            ['Nasi Lemak Special',  'Fragrant coconut rice with sambal, anchovies, egg and peanuts.', 12.00, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80'],
            ['Char Kuey Teow',      'Stir-fried flat rice noodles with prawns and bean sprouts.',     13.50, 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=400&q=80'],
            ['Laksa Lemak',         'Rich coconut curry noodle soup with prawns.',                    14.00, 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=400&q=80'],
            ['Fried Rice',          'Wok-fried rice with egg, vegetables and soy sauce.',             10.50, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&q=80'],
            ['Teh Tarik',           'Pulled milk tea, Malaysian style.',                               3.50, 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400&q=80'],
        ];
        foreach ($asianItems as $i) { $im->execute([$rids[1],$i[0],$i[1],$i[2],$i[3],1]); }

        // Healthy Bowl menu
        $healthyItems = [
            ['Grilled Chicken Salad', 'Grilled chicken breast over mixed greens with vinaigrette.',  15.90, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80'],
            ['Quinoa Bowl',           'Quinoa with roasted vegetables, chickpeas and tahini.',        16.90, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80'],
            ['Acai Bowl',             'Acai blend topped with granola, banana and fresh fruits.',     14.90, 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=400&q=80'],
            ['Avocado Toast',         'Multigrain toast with mashed avocado and poached egg.',        12.90, 'https://images.unsplash.com/photo-1588137378633-dea1336ce1e2?w=400&q=80'],
            ['Green Smoothie',        'Spinach, banana, mango and almond milk blend.',                 9.90, 'https://images.unsplash.com/photo-1610970881699-44a5587cabec?w=400&q=80'],
        ];
        foreach ($healthyItems as $i) { $im->execute([$rids[2],$i[0],$i[1],$i[2],$i[3],1]); }

        // Dessert House menu
        $dessertItems = [
            ['Chocolate Lava Cake',  'Warm chocolate cake with a molten chocolate centre.',          12.90, 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=400&q=80'],
            ['Bubble Tea',           'Classic milk tea with tapioca pearls. Choice of flavour.',      7.90, 'https://images.unsplash.com/photo-1558857563-b371033873b8?w=400&q=80'],
            ['Crepe Cake',           'Layered crepe cake with fresh cream and strawberries.',         14.90, 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=400&q=80'],
            ['Ice Cream Waffle',     'Belgian waffle with two scoops of ice cream.',                  11.90, 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&q=80'],
            ['Iced Matcha Latte',    'Premium matcha powder with steamed milk over ice.',              8.90, 'https://images.unsplash.com/photo-1536935338788-846bb9981813?w=400&q=80'],
        ];
        foreach ($dessertItems as $i) { $im->execute([$rids[3],$i[0],$i[1],$i[2],$i[3],1]); }

        // Mamak Corner menu
        $mamakItems = [
            ['Roti Canai',           'Flaky flatbread served with dhal and curry sauce.',              3.50, 'https://images.unsplash.com/photo-1596560548464-f010549b84d7?w=400&q=80'],
            ['Mee Goreng Mamak',     'Spicy stir-fried yellow noodles with egg and vegetables.',      8.90, 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=400&q=80'],
            ['Maggi Goreng',         'Fried instant noodles with egg, vegetables and chilli.',         7.90, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&q=80'],
            ['Teh Tarik',            'Frothy pulled milk tea.',                                         3.00, 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400&q=80'],
            ['Banana Fritters',      'Deep-fried banana fritters with a crispy golden batter.',        5.90, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&q=80'],
        ];
        foreach ($mamakItems as $i) { $im->execute([$rids[4],$i[0],$i[1],$i[2],$i[3],1]); }

        // Sample delivered order for customer demo
        $custId = (int)$pdo->query("SELECT `id` FROM `users` WHERE `email`='customer@foodflow.com'")->fetchColumn();
        $pdo->prepare('INSERT INTO `orders` (`customer_id`,`restaurant_id`,`total_amount`,`delivery_fee`,`delivery_address`,`payment_method`,`status`) VALUES (?,?,?,?,?,?,?)')
            ->execute([$custId,$rids[0],28.80,5.00,'No. 12, Jalan Teknologi 3, Cyber 7, 63000 Cyberjaya, Selangor','cash','delivered']);
        $oid = (int)$pdo->lastInsertId();
        $firstItem = (int)$pdo->query("SELECT `id` FROM `menu_items` WHERE `restaurant_id`={$rids[0]} LIMIT 1")->fetchColumn();
        $pdo->prepare('INSERT INTO `order_items` (`order_id`,`menu_item_id`,`quantity`,`unit_price`,`subtotal`) VALUES (?,?,?,?,?)')
            ->execute([$oid,$firstItem,1,14.90,14.90]);

        echo '<p class="ok">Sample data seeded: 5 restaurants, 25 menu items, 7 users.</p>';
    } else {
        echo '<p class="warn">Tables already have data - skipped seeding.</p>';
    }

    echo '<hr><h2 class="ok">Setup Complete!</h2>';
} catch (\PDOException $e) {
    echo '<p class="err">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<div class="box">
<strong>Login Credentials:</strong>
<pre>
Admin
  Email    : admin@foodflow.com
  Password : Admin123!

Restaurant Partner (Burger Hub)
  Email    : partner@burgerhub.com
  Password : Partner123!

Restaurant Partner (Asian Kitchen)
  Email    : partner@asiankitchen.com
  Password : Partner123!

Customer
  Email    : customer@foodflow.com
  Password : Customer123!
</pre>
</div>

<p><a href="index.php">Go to FoodFlow Home</a> | <a href="auth.php">Go to Login</a></p>

<?php endif; ?>
</body>
</html>
