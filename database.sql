-- ==============================
-- HUNGRY FOOD - COMPLETE DATABASE
-- Restaurant Management System
-- ==============================

CREATE DATABASE IF NOT EXISTS `hungry_food`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `hungry_food`;

-- ==============================
-- TABLE: categories
-- ==============================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `display_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `is_active` (`is_active`),
    KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: menu_items
-- ==============================
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_code` VARCHAR(20) NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `price_pkr` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `category_slug` VARCHAR(50) DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `is_vegetarian` TINYINT(1) DEFAULT 0,
    `is_spicy` TINYINT(1) DEFAULT 0,
    `is_popular` TINYINT(1) DEFAULT 0,
    `calories` INT(11) DEFAULT NULL,
    `cooking_time` INT(11) DEFAULT NULL,
    `ingredients` TEXT DEFAULT NULL,
    `is_available` TINYINT(1) DEFAULT 1,
    `stock_quantity` INT(11) DEFAULT 100,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `item_code` (`item_code`),
    KEY `category_slug` (`category_slug`),
    KEY `is_available` (`is_available`),
    KEY `is_popular` (`is_popular`),
    CONSTRAINT `fk_menu_category` FOREIGN KEY (`category_slug`)
        REFERENCES `categories` (`slug`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: users
-- ==============================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(50) DEFAULT NULL,
    `user_type` ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `user_type` (`user_type`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: auth_tokens
-- ==============================
CREATE TABLE IF NOT EXISTS `auth_tokens` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `selector` VARCHAR(64) NOT NULL,
    `hashed_validator` VARCHAR(128) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `selector` (`selector`),
    KEY `user_id` (`user_id`),
    KEY `expires_at` (`expires_at`),
    CONSTRAINT `fk_auth_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================
-- TABLE: orders
-- ==============================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR(50) NOT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `customer_name` VARCHAR(100) DEFAULT NULL,
    `customer_email` VARCHAR(100) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `customer_address` TEXT DEFAULT NULL,
    `customer_city` VARCHAR(50) DEFAULT NULL,
    `items_json` LONGTEXT DEFAULT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_method` VARCHAR(30) DEFAULT NULL,
    `order_type` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('pending', 'processing', 'delivered', 'cancelled') DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: order_items
-- ==============================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `menu_item_id` INT(11) DEFAULT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `item_code` VARCHAR(20) DEFAULT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `special_instructions` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `menu_item_id` (`menu_item_id`),
    CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`)
        REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_item_menu` FOREIGN KEY (`menu_item_id`)
        REFERENCES `menu_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: reservations
-- ==============================
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `reservation_id` VARCHAR(50) NOT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `guests` INT(11) NOT NULL,
    `reservation_date` DATE NOT NULL,
    `reservation_time` TIME NOT NULL,
    `table_type` VARCHAR(50) DEFAULT 'any',
    `occasion` VARCHAR(50) DEFAULT NULL,
    `special_requests` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `reservation_id` (`reservation_id`),
    KEY `user_id` (`user_id`),
    KEY `reservation_date` (`reservation_date`),
    KEY `status` (`status`),
    CONSTRAINT `fk_reservation_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================
-- TABLE: reservation_logs
-- ==============================
CREATE TABLE IF NOT EXISTS `reservation_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `reservation_id` INT(11) DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `reservation_id` (`reservation_id`),
    KEY `action` (`action`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `fk_log_reservation` FOREIGN KEY (`reservation_id`)
        REFERENCES `reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: contacts
-- ==============================
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `contact_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `subject` VARCHAR(50) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `admin_notes` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `contact_id` (`contact_id`),
    KEY `status` (`status`),
    KEY `priority` (`priority`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: contact_logs
-- ==============================
CREATE TABLE IF NOT EXISTS `contact_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `contact_id` INT(11) DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contact_id` (`contact_id`),
    KEY `action` (`action`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `fk_log_contact` FOREIGN KEY (`contact_id`)
        REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: settings
-- ==============================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string',
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`),
    KEY `is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================
-- TABLE: currency_rates
-- ==============================
CREATE TABLE IF NOT EXISTS `currency_rates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `from_currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
    `to_currency` VARCHAR(3) NOT NULL DEFAULT 'PKR',
    `exchange_rate` DECIMAL(10,4) NOT NULL DEFAULT 280.0000,
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `from_to_currency` (`from_currency`, `to_currency`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: food_items
-- ==============================
CREATE TABLE IF NOT EXISTS `food_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `description` TEXT DEFAULT NULL,
    `image` VARCHAR(500) DEFAULT NULL,
    `stock_quantity` INT(11) DEFAULT 0,
    `is_available` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category` (`category`),
    KEY `is_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: offers
-- ==============================
CREATE TABLE IF NOT EXISTS `offers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `code` VARCHAR(50) NOT NULL,
    `discount_type` ENUM('percentage', 'fixed', 'delivery') DEFAULT 'percentage',
    `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
    `max_discount` DECIMAL(10,2) DEFAULT NULL,
    `usage_limit` INT(11) DEFAULT NULL,
    `used_count` INT(11) DEFAULT 0,
    `valid_from` DATE DEFAULT NULL,
    `valid_till` DATE DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    KEY `is_active` (`is_active`),
    KEY `valid_from_till` (`valid_from`, `valid_till`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- TABLE: catering_orders
-- ==============================
CREATE TABLE IF NOT EXISTS `catering_orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR(50) NOT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(100) DEFAULT NULL,
    `package_type` VARCHAR(50) NOT NULL,
    `persons` INT(11) NOT NULL,
    `price_per_person` DECIMAL(10,2) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME DEFAULT NULL,
    `event_type` VARCHAR(50) DEFAULT NULL,
    `venue_address` TEXT DEFAULT NULL,
    `special_requests` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`),
    KEY `event_date` (`event_date`),
    KEY `status` (`status`),
    CONSTRAINT `fk_catering_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================
-- INSERT SAMPLE DATA
-- ==============================

INSERT INTO `categories` (`slug`, `name`, `description`, `icon`, `display_order`, `is_active`) VALUES
('pizza', 'Pizza', 'Delicious hand-crafted pizzas with fresh toppings', 'fas fa-pizza-slice', 1, 1),
('burgers', 'Burgers', 'Juicy burgers made with premium beef', 'fas fa-hamburger', 2, 1),
('main-course', 'Main Course', 'Hearty main course dishes', 'fas fa-utensils', 3, 1),
('appetizers', 'Appetizers', 'Start your meal with our tasty appetizers', 'fas fa-carrot', 4, 1),
('desserts', 'Desserts', 'Sweet treats to finish your meal', 'fas fa-ice-cream', 5, 1),
('drinks', 'Drinks', 'Refreshing beverages', 'fas fa-cocktail', 6, 1);

INSERT INTO `menu_items`
(`item_code`, `name`, `description`, `price_usd`, `price_pkr`, `category_slug`,
 `image_url`, `is_vegetarian`, `is_spicy`, `is_popular`, `calories`,
 `cooking_time`, `ingredients`, `is_available`, `stock_quantity`) VALUES
('PIZ001', 'Margherita Pizza', 'Fresh mozzarella, tomatoes, and basil on signature crust',
 14.99, 3500.00, 'pizza',
 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 1, 0, 1, 800, 15, 'Mozzarella, Tomatoes, Basil, Olive Oil', 1, 50),
('PIZ002', 'Pepperoni Pizza', 'Classic pepperoni with mozzarella cheese',
 16.99, 4000.00, 'pizza',
 'https://images.unsplash.com/photo-1628840042765-356cda07504e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 1, 1, 950, 18, 'Pepperoni, Mozzarella, Tomato Sauce', 1, 40),
('BUR001', 'Classic Cheeseburger', 'Beef patty with cheese, lettuce, tomato & special sauce',
 12.99, 3200.00, 'burgers',
 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 1, 650, 20, 'Beef patty, Cheddar, Lettuce, Tomato, Secret sauce', 1, 40),
('BUR002', 'BBQ Bacon Burger', 'Smoky BBQ sauce with crispy bacon',
 14.99, 3600.00, 'burgers',
 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 0, 780, 22, 'Beef patty, Bacon, BBQ Sauce, Onion Rings', 1, 35),
('MAI001', 'Grilled Ribeye Steak', '12oz prime ribeye grilled to perfection with herb butter',
 28.99, 6500.00, 'main-course',
 'https://images.unsplash.com/photo-1550547660-d9450f859349?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 1, 950, 25, 'Ribeye steak, Herb butter, Garlic, Rosemary', 1, 30),
('MAI002', 'Fettuccine Alfredo', 'Creamy Alfredo sauce with grilled chicken',
 16.99, 3800.00, 'main-course',
 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 0, 720, 20, 'Fettuccine, Cream, Parmesan, Chicken', 1, 45),
('APP001', 'Vegetable Spring Rolls', 'Crispy spring rolls filled with fresh vegetables',
 8.99, 2200.00, 'appetizers',
 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 1, 0, 1, 350, 10, 'Cabbage, Carrots, Bell peppers, Spring onion wrappers', 1, 60),
('APP002', 'Chicken Wings', 'Spicy buffalo wings with blue cheese dip',
 10.99, 2600.00, 'appetizers',
 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 1, 1, 480, 15, 'Chicken wings, Buffalo sauce, Blue cheese', 1, 50),
('DES001', 'Chocolate Lava Cake', 'Warm chocolate cake with molten center',
 8.99, 2200.00, 'desserts',
 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 1, 450, 12, 'Dark chocolate, Butter, Eggs, Sugar, Flour', 1, 35),
('DES002', 'New York Cheesecake', 'Classic creamy cheesecake with berry compote',
 7.99, 2000.00, 'desserts',
 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 0, 0, 0, 380, 10, 'Cream cheese, Graham crackers, Berries', 1, 30),
('DRI001', 'Fresh Orange Juice', 'Freshly squeezed oranges',
 4.99, 1200.00, 'drinks',
 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 1, 0, 0, 120, 5, 'Fresh oranges', 1, 100),
('DRI002', 'Mango Lassi', 'Traditional Pakistani mango yogurt drink',
 3.99, 1000.00, 'drinks',
 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
 1, 0, 1, 180, 5, 'Mango, Yogurt, Sugar, Cardamom', 1, 80);
INSERT INTO `currency_rates` (`from_currency`, `to_currency`, `exchange_rate`, `is_active`) VALUES
('USD', 'PKR', 280.0000, 1),
('PKR', 'USD', 0.0036, 1);

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `is_public`) VALUES
('site_name', 'Hungry Food', 'string', 1),
('support_email', 'support@hungryfood.com', 'string', 1),
('admin_email', 'admin@hungryfood.com', 'string', 0),
('phone', '0317-0544863', 'string', 1),
('whatsapp', '+92 317 0544863', 'string', 1),
('address', 'MM Alam Road, Lahore, Punjab, Pakistan', 'string', 1),
('currency_default', 'USD', 'string', 1),
('tax_rate', '8.25', 'number', 0),
('delivery_fee', '9.00', 'number', 1),
('min_order_amount', '20.00', 'number', 1),
('max_file_size', '5242880', 'number', 0),
('allowed_file_types', '["jpg", "jpeg", "png", "pdf"]', 'json', 0),
('opening_hours',
 '{"monday":"10:00-22:00","tuesday":"10:00-22:00","wednesday":"10:00-22:00","thursday":"10:00-22:00","friday":"10:00-23:00","saturday":"10:00-23:00","sunday":"11:00-22:00"}',
 'json', 1);

INSERT INTO `offers`
(`title`, `description`, `code`, `discount_type`, `discount_value`,
 `min_order_amount`, `max_discount`, `usage_limit`, `valid_from`, `valid_till`, `is_active`) VALUES
('Weekend Special', 'Get 20% off on all orders above $50',
 'WEEKEND20', 'percentage', 20.00, 50.00, 20.00, 100, '2024-01-01', '2024-12-31', 1),
('Free Delivery', 'Free delivery on orders above $30',
 'FREEDEL', 'delivery', 5.00, 30.00, NULL, 200, '2024-01-01', '2024-12-31', 1),
('New User Discount', '$5 off on your first order',
 'WELCOME5', 'fixed', 5.00, 15.00, NULL, 1, '2024-01-01', '2024-12-31', 1);


-- ==============================
-- END OF DATABASE SETUP
-- ==============================



