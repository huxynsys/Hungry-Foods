<?php
// install.php - Run this once to create database tables
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/db.php';

// Production guard: refuse to re-run once the database is already installed.
$tableCheck = $conn->query("SHOW TABLES LIKE 'menu_items'");
if ($tableCheck && $tableCheck->rowCount() > 0) {
    http_response_code(403);
    die('<h2>Already installed</h2><p>The Hungry Food database tables already exist, '
      . 'so the installer has been disabled. For security, delete <code>install.php</code> '
      . 'from your live server after setup.</p>');
}

echo "<h2>Installing Hungry Food Database...</h2>";

try {
    // Create categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        display_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✓ Categories table created<br>";
    
    // Create menu_items table
    $sql = "CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_code VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price_usd DECIMAL(10,2) NOT NULL,
        price_pkr DECIMAL(10,2) NOT NULL,
        category_slug VARCHAR(50),
        image_url VARCHAR(500),
        is_vegetarian BOOLEAN DEFAULT FALSE,
        is_spicy BOOLEAN DEFAULT FALSE,
        is_popular BOOLEAN DEFAULT FALSE,
        calories INT,
        cooking_time INT,
        ingredients TEXT,
        is_available BOOLEAN DEFAULT TRUE,
        stock_quantity INT DEFAULT 100,
        FOREIGN KEY (category_slug) REFERENCES categories(slug)
    )";
    $conn->exec($sql);
    echo "✓ Menu items table created<br>";
    
    // Insert categories
    $categories = [
        ['pizza', 'Pizza', 1],
        ['burgers', 'Burgers', 2],
        ['main-course', 'Main Course', 3],
        ['appetizers', 'Appetizers', 4],
        ['desserts', 'Desserts', 5],
        ['drinks', 'Drinks', 6]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (slug, name, display_order) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "✓ Sample categories inserted<br>";
    
    // Insert menu items
    $items = [
        ['PIZ001', 'Margherita Pizza', 'Fresh mozzarella, tomatoes, and basil on signature crust', 14.99, 3500, 'pizza', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38', 1, 1, 800, 15, 'Mozzarella, Tomatoes, Basil, Olive Oil', 50],
        ['BUR001', 'Classic Cheeseburger', 'Beef patty with cheese, lettuce, tomato & special sauce', 12.99, 3200, 'burgers', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd', 0, 1, 650, 20, 'Beef patty, Cheddar, Lettuce, Tomato, Secret sauce', 40],
        ['MAI001', 'Grilled Ribeye Steak', '12oz prime ribeye grilled to perfection with herb butter', 28.99, 6500, 'main-course', 'https://images.unsplash.com/photo-1550547660-d9450f859349', 0, 1, 950, 25, 'Ribeye steak, Herb butter, Garlic, Rosemary', 30],
        ['APP001', 'Vegetable Spring Rolls', 'Crispy spring rolls filled with fresh vegetables', 8.99, 2200, 'appetizers', 'https://images.unsplash.com/photo-1563379091339-03246963d9d6', 1, 0, 350, 10, 'Cabbage, Carrots, Bell peppers, Spring onion wrappers', 60],
        ['DES001', 'Chocolate Lava Cake', 'Warm chocolate cake with molten center', 8.99, 2200, 'desserts', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587', 1, 1, 450, 12, 'Dark chocolate, Butter, Eggs, Sugar, Flour', 35],
        ['DRI001', 'Fresh Orange Juice', 'Freshly squeezed oranges', 4.99, 1200, 'drinks', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd', 1, 0, 120, 5, 'Fresh oranges', 100]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO menu_items 
        (item_code, name, description, price_usd, price_pkr, category_slug, image_url, is_vegetarian, is_popular, calories, cooking_time, ingredients, stock_quantity) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $stmt->execute($item);
    }
    echo "✓ Sample menu items inserted<br>";
    
    echo "<h3 style='color: green;'>✅ Installation Complete!</h3>";
    echo "<p><a href='menu.php'>View Your Menu</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}
?>