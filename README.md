# 🍔 Hungry Food — Restaurant Management & Food Ordering System

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

**A full-featured restaurant management and food ordering platform with real-time order tracking, AI-powered chatbot assistance, and comprehensive admin controls.**

[Features](#-features) • [Technology Stack](#-technology-stack) • [Architecture & Flow](#-architecture--application-flow) • [Project Structure](#-project-structure) • [Installation](#-installation)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Architecture & Application Flow](#-architecture--application-flow)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [User Guide](#-user-guide)
- [Admin Panel](#-admin-panel)
- [AI Chatbot Integration](#-ai-chatbot-integration)
- [Security Features](#-security-features)
- [Responsive Design](#-responsive-design)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

---

## 🎯 Overview

## 🚀 Features

### Customer-Facing Features

#### 🛍️ E-Commerce & Ordering
- **Dynamic Menu System** — Category filtering, search functionality, real-time availability status
- **Shopping Cart** — Session-based cart with add/remove items, quantity adjustment, price calculation
- **Order Management** — Complete checkout flow with delivery/pickup options, order confirmation
- **Order Tracking** — Real-time order status updates (Pending → Processing → Delivered/Cancelled)
- **Multi-Currency Support** — USD ($) and PKR (Rs) with dynamic exchange rate conversion

#### 📅 Reservations & Catering
- **Table Reservations** — Book tables with date/time selection, guest count, special requests
- **Catering Services** — Package-based catering with per-person pricing (Silver, Gold, Platinum packages)
- **Event Planning** — Special event requests with custom menu selections

#### 💬 Customer Support
- **AI Chatbot (Zara)** — Powered by Anthropic Claude API for menu questions, orders, reservations
- **Contact Form** — Inquiry submission with status tracking
- **FAQ System** — Common questions and answers

#### 👤 User Account Management
- **User Registration** — Secure account creation with email validation
- **Login / Logout** — Session-based authentication with "Remember Me" functionality
- **Profile Management** — Update personal information, view order history
- **Password Recovery** — Secure password reset via email

### Admin Panel Features

#### 📊 Dashboard & Analytics
- **Revenue Overview** — Daily, weekly, monthly revenue charts with Chart.js
- **Order Statistics** — Total orders, pending orders, delivered orders count
- **Customer Insights** — New customers, returning customers analytics
- **Popular Items** — Best-selling menu items tracking

#### 📦 Order Management
- **Order Dashboard** — View all orders with filtering and search
- **Status Updates** — Update order status (Pending, Processing, Delivered, Cancelled)
- **Order Details** — Complete order information with customer details

#### 🍽️ Menu Management
## 💻 Technology Stack

### Backend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 7.4+ | Server-side scripting, business logic |
| **MySQL** | 5.7+ | Relational database management |
| **PDO** | — | Database abstraction layer with prepared statements |
| **Apache** | 2.4+ | Web server (via XAMPP) |
| **PHP Sessions** | — | User authentication and state management |

**Backend Architecture** — The backend follows a lightweight MVC-style structure. `backend/db.php` is the central PDO connection file that auto-detects the environment (local XAMPP vs. production) via environment variables. It also exposes reusable helper functions: `getDatabaseConnection()`, `executeQuery()`, `fetchAllRows()`, `insertData()`, `updateData()`, and currency helpers (`getExchangeRate()`, `convertCurrency()`, `formatPrice()`). All queries use **prepared statements** with bound parameters to prevent SQL injection.

### Frontend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **HTML5** | — | Semantic markup structure |
| **CSS3** | — | Custom properties, Flexbox, Grid, animations |
| **Bootstrap** | 5.3 | Responsive UI framework, grid system, components |
| **JavaScript** | ES6+ | Client-side interactivity, AJAX, DOM manipulation |
| **Chart.js** | 3.x | Data visualization for admin dashboard |
| **Font Awesome** | 6.4.0 | Icon library |
| **Google Fonts** | — | Poppins (headings) & Inter (body) typography |

**Frontend Architecture** — A custom design system lives in `assets/css/modern-ui.css` with CSS custom properties (variables) for colors, spacing, shadows, and transitions. Client-side JavaScript is split into focused modules: `cart.js` (shopping cart logic), `form-validation.js` (validation), `responsive.js` (mobile interactions), and `chatbot.js` (AI chatbot UI). AJAX calls use the `fetch()` API for cart updates and chatbot messages.

### External APIs & Services

| Service | Purpose | Integration Point |
|---------|---------|-------------------|
| **Anthropic Claude API** | AI chatbot (Zara) | `ai.php`, `chatbot.php` |
| **Unsplash API** | Food imagery | Menu item placeholder images |
| **Google Fonts CDN** | Typography | All pages |
## 🏗️ Architecture & Application Flow

### System Architecture Overview

The application follows a classic **3-tier architecture** (Presentation → Application → Data), with external service integration for AI and media.

```
┌─────────────────────────────────────────────────────────────┐
│                      CLIENT / PRESENTATION LAYER             │
│              (HTML5 + CSS3 + Bootstrap 5 + JS ES6+)          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ Homepage │  │   Menu   │  │   Cart   │  │  Orders  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │Reservation│ │ Catering │  │ Contact  │  │ Chatbot  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
└─────────────────────────────────────────────────────────────┘
                           ↓ HTTP / HTTPS
┌─────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER (PHP 7.4+)              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ index.php│  │ menu.php │  │ order.php │  │  admin/   │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│  Common: backend/db.php · backend/session.php · helpers     │
└─────────────────────────────────────────────────────────────┘
                           ↓ PDO (prepared statements)
┌─────────────────────────────────────────────────────────────┐
│                     DATA LAYER (MySQL 5.7+)                 │
│  ┌────────┐ ┌───────────┐ ┌────────┐ ┌─────────────┐       │
│  │ users  │ │ menu_items│ │ orders │ │ reservations│       │
│  └────────┘ └───────────┘ └────────┘ └─────────────┘       │
│  ┌────────┐ ┌───────────┐ ┌────────┐ ┌─────────────┐       │
│  │contacts│ │categories │ │settings│ │currency_rates│       │
│  └────────┘ └───────────┘ └────────┘ └─────────────┘       │
└─────────────────────────────────────────────────────────────┘
                           ↓ API Calls
┌─────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │  Anthropic   │  │   Unsplash   │  │ Email (SMTP) │       │
│  │  Claude API  │  │     API      │  │              │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
```

### Request Lifecycle

Every request follows the same predictable lifecycle:

```
1. Browser request  →  http://localhost/hungry-food/menu.php
2. PHP boots        →  session_start() + include backend/db.php
### User Flow Diagrams

#### 1. Customer Ordering Flow
```
[Homepage] → [Menu Page] → [Add to Cart] → [View Cart]
     ↓             ↓              ↓              ↓
[Register /   [Filter by    [Update         [Proceed to
 Login]        Category]      Quantity]       Checkout]
     ↓             ↓              ↓              ↓
[Account] → [Select Item] → [Cart Session] → [Order Form]
                                              ↓
                                        [Place Order]
                                              ↓
                                    [Order Confirmation]
                                              ↓
                                    [Email Notification]
```

#### 2. Admin Management Flow
```
[Admin Login] → [Dashboard] → [Select Module]
      ↓              ↓               ↓
[Authenticate] [View Stats]   [Orders / Menu / Reservations]
      ↓              ↓               ↓
[Session Set]  [Charts]        [CRUD Operations]
      ↓              ↓               ↓
[Dashboard]    [Analytics]     [Database Updates]
```

#### 3. AI Chatbot Interaction Flow
```
[User Opens Chat] → [Send Message] → [ai.php Processing]
        ↓                  ↓                    ↓
[Chatbot UI]      [AJAX Request]    [Call Claude API]
        ↓                  ↓                    ↓
[Display Response] ← [Parse Response] ← [AI Response]
```

#### 4. Reservation Flow
```
[Reservation Page] → [Fill Form] → [Submit] → [Store in DB]
        ↓               ↓            ↓            ↓
[Pick Date/Time]  [Guest Count] [Validate]  [Admin Review]
## 📁 Project Structure

```
hungry-food/
│
├── 📂 admin/                          # Admin Panel Module
│   ├── admin-login.php               # Admin authentication page
│   ├── dashboard.php                 # Admin dashboard (stats, charts, CRUD)
│   ├── manage-orders.php             # View / update customer orders
│   ├── admin-reservations.php        # Manage table reservations
│   ├── manage-contacts.php           # Handle contact inquiries
│   └── manage-menu.php               # CRUD for menu items + image upload
│
├── 📂 backend/                       # Backend Core (shared PHP)
│   ├── db.php                        # PDO connection + helper functions
│   ├── session.php                   # Session management & auth helpers
│   ├── auth.php                      # User authentication logic
│   ├── functions.php                 # Common utility functions
│   └── config.php                    # Application configuration
│
├── 📂 assets/                        # Static Assets
│   ├── 📂 css/                       # Stylesheets
│   │   ├── modern-ui.css             # Main UI styles + CSS variables
│   │   ├── responsive.css            # Mobile-first responsive rules
│   │   └── animations.css            # CSS animations & transitions
│   ├── 📂 js/                        # JavaScript modules
│   │   ├── cart.js                   # Shopping cart logic
│   │   ├── form-validation.js        # Client-side form validation
│   │   ├── responsive.js             # Mobile interaction enhancements
│   │   └── chatbot.js                # AI chatbot interface logic
│   ├── 📂 images/                    # Static site images
│   ├── 📂 menu/                      # Uploaded menu item images
│   └── 📂 uploads/                    # User-uploaded files
│
├── 📂 includes/                      # Reusable Components
│   ├── header.php                    # Site <head> + opening layout
│   ├── footer.php                    # Site footer + closing scripts
│   ├── navbar.php                    # Navigation bar
│   └── chatbot-widget.php            # Floating chatbot component
│
├── 📄 index.php                      # Homepage (hero, categories, popular items)
├── 📄 menu.php                       # Menu page (filter, search, add-to-cart)
├── 📄 order.php                      # Shopping cart + checkout
├── 📄 order_confirmation.php         # Order success / confirmation
├── 📄 reservation.php               # Table reservation form
├── 📄 catering.php                   # Catering services & packages
├── 📄 contact.php                    # Contact form
├── 📄 contact_status.php            # Track inquiry status
├── 📄 about.php                      # About us page
├── 📄 login.php                      # User login & registration
├── 📄 ai.php                         # AI chatbot API endpoint (Claude)
├── 📄 chatbot.php                    # Chatbot interface page
├── 📄 install.php                    # Database installation / seeding
├── 📄 check_updates.php             # AJAX update checker
├── 📄 update_quantity.php           # Cart quantity AJAX handler
├── 📄 README.md                      # Project documentation (this file)
└── 📄 .htaccess                      # Apache rewrite rules & security
```

### Folder Responsibilities

| Folder / File | Responsibility |
|---------------|----------------|
| `admin/` | All administrative pages (authentication-gated) |
| `backend/` | Shared PHP: DB connection, sessions, auth, helpers |
| `assets/css/` | All stylesheets (UI, responsive, animations) |
| `assets/js/` | Client-side JavaScript modules |
## 🗄️ Database Schema

### Database Configuration
The database connection is configured in `backend/db.php`. It auto-detects the environment: locally it uses XAMPP defaults; in production it reads environment variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).

```php
// Local (XAMPP) — works out of the box
$host     = 'localhost';
$database = 'hungry_food';
$username = 'root';
$password = '';

// Production — set environment variables via hosting panel / .htaccess
// DB_HOST, DB_NAME, DB_USER, DB_PASS
```

### Database Tables

| Table | Description |
|-------|-------------|
| `categories` | Food categories (Pizza, Burgers, Desserts, etc.) |
| `menu_items` | Menu items with prices, descriptions, and metadata |
| `orders` | Customer orders with items, totals, and status (uses `created_at`) |
| `reservations` | Table reservation requests |
| `contacts` | Customer contact form submissions |
| `contact_logs` | Audit log for contact inquiry actions |
| `reservation_logs` | Audit log for reservation actions |
| `users` | Registered user accounts |
| `auth_tokens` | "Remember Me" authentication tokens |
| `settings` | Application settings key-value store |
| `currency_rates` | Currency exchange rates (USD ↔ PKR) |

### Key Column Note
The `orders` table uses `created_at` (NOT `order_date`) for timestamps. All dashboard and reporting queries reference `created_at` to avoid "Column not found" errors.

### Sample Data
The `install.php` script automatically populates:
- 6 food categories
- 6 sample menu items with images
- Default admin credentials

---

## ⚙️ Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or similar PHP/MySQL stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge recommended)

### Access URLs
- **Frontend**: `http://localhost:8080/hungry-food/` or `http://localhost/hungry-food/`
- **Admin Panel**: `http://localhost:8080/hungry-food/admin/admin-login.php`
- **AI Chatbot**: `http://localhost:8080/hungry-food/ai.php`

### Step-by-Step Installation

1. **Clone or download the project**
   ```bash
   git clone https://github.com/yourusername/hungry-food.git
   ```

2. **Move to XAMPP htdocs directory**
   ```bash
   mv hungry-food /path/to/xampp/htdocs/
   ```

3. **Start XAMPP services**
   - Open XAMPP Control Panel
   - Start **Apache** module
## 🔧 Configuration

### Database Settings
Edit `backend/db.php`:
```php
$host     = 'localhost';
$username = 'root';
$password = '';
$database = 'hungry_food';
```

### Currency Settings
All prices are in USD ($) by default. The menu page displays prices in USD, with an option to toggle to PKR (Rs) using live exchange rates from the `currency_rates` table.

Available currency helpers in `backend/db.php`:
```php
getExchangeRate($from, $to)   // Fetch latest rate from DB
convertCurrency($amount, ...)  // Convert between currencies
formatPrice($amount, $currency) // Format with symbol ($ or Rs)
getCurrentCurrency()           // Get session currency preference
toggleCurrency()              // Switch USD ↔ PKR
```

### Email Configuration
Update settings in the `settings` table for:
- `support_email` — Customer support email
- `admin_email` — Admin notification email
- `site_name` — Restaurant name

### Session Configuration
Session settings are handled in `backend/session.php`:
- Session timeout: 30 minutes
- Secure session handling with SHA-512 hashing
- "Remember Me" functionality with secure tokens
- Session regeneration on login (prevents session fixation)

## 👤 User Guide

### Browsing the Menu
1. Navigate to **Menu** in the navigation bar
2. Use category filters to browse specific food types
3. Use the search bar to find specific items
4. Click **Add to Cart** to add items to your shopping cart

### Placing an Order
1. Add items to your cart from the menu
2. Click the cart icon to review your order
3. Adjust quantities or remove items as needed
4. Proceed to checkout
5. Fill in delivery information
6. Select payment method (Cash on Delivery / Card)
7. Confirm your order — you'll receive an order confirmation page

### Making a Reservation
1. Go to the **Reservation** page
2. Fill in your details (name, phone, email)
3. Select date, time, and number of guests
4. Choose table type and occasion
5. Add any special requests
6. Submit the reservation — admin will review and confirm

### Catering Services
1. Go to the **Catering** page
2. Choose a package (Silver, Gold, or Platinum)
3. Enter the number of guests
4. Select event date and requirements
5. Submit the catering request

### Tracking an Inquiry
1. Submit a contact form and note your **Contact ID**
2. Visit the **Track Your Inquiry** page
3. Enter your Contact ID to check the current status

---

## 🔐 Admin Panel

### Accessing the Admin Panel
1. Register your first account at `http://localhost/hungry-food/login.php?register=1`
   — **the first registered account automatically becomes an administrator.**
2. Access the admin panel at: `http://localhost:8080/hungry-food/admin/admin-login.php`
3. Default credentials (set by install script):
   - **Username:** `admin`
   - **Password:** `admin123`

### Admin Features

#### Dashboard
- Revenue overview with monthly charts (Chart.js)
- Order statistics (pending, delivered, total)
- Recent orders and reservations tables
- Customer analytics

#### Order Management
- View all orders with full details
- Update order status (Pending → Processing → Delivered / Cancelled)
- View customer information and order items
- Price formatting via `number_format()`

#### Menu Management
- Add new menu items with image uploads
- Edit existing items
- Manage categories
- Set pricing in USD with automatic PKR conversion

#### Reservation Management
- View all reservation requests
- Confirm / cancel / complete reservations
- Filter by date and status

---
## 🔒 Security Features

- **Prepared Statements** — All database queries use PDO prepared statements to prevent SQL injection
- **CSRF Protection** — Token-based form validation (`$_SESSION['csrf_token']`) on all admin forms
- **Input Sanitization** — All user inputs are sanitized and validated
- **Password Hashing** — Bcrypt password hashing (`PASSWORD_DEFAULT`)
- **Session Security** — Session regeneration on login to prevent session fixation
- **XSS Prevention** — Output encoding with `htmlspecialchars()` and JavaScript escaping
- **Security Headers** — `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`
- **API Key Protection** — Environment variable storage for API keys (never hardcoded)
- **CORS Restrictions** — Restricted to same-origin requests
- **Error Handling** — Proper error handling without exposing sensitive info in production
- **Input Length Limits** — Message and input length limits to prevent abuse
- **File Upload Validation** — Allowed extensions (`jpg`, `jpeg`, `png`, `webp`) and unique filenames

### CSRF Token Example
```php
// Generate once per session
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verify on every POST request
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid request.');
}
```

---

## 📱 Responsive Design

The application is fully responsive with a **mobile-first** approach.

### Breakpoints
| Breakpoint | Range | Target |
|------------|-------|--------|
| Extra Small | < 576px | Portrait phones |
| Small | 576px – 767px | Landscape phones |
| Medium | 768px – 991px | Tablets |
| Large | 992px – 1199px | Desktops |
| Extra Large | ≥ 1200px | Large desktops |

### Responsive Features
- **Mobile-First CSS** — Base styles for mobile, enhanced for larger screens
- **Touch-Friendly** — 44px minimum touch targets (Apple's recommendation)
- **Flexible Grid** — Bootstrap 5 grid system with custom breakpoints
- **Responsive Typography** — Uses `clamp()` for fluid font scaling
- **Adaptive Navigation** — Collapsible navbar with mobile menu
- **Lazy Loading** — Images load as they enter viewport
- **Scroll Animations** — Intersection Observer-based animations
## 🛠️ Troubleshooting

### Common Issues

**Database Connection Failed**
- Verify XAMPP MySQL service is running
- Check credentials in `backend/db.php`
- Ensure database `hungry_food` exists

**Session Already Started Error**
- Ensure no whitespace before `<?php` in files
- Check for duplicate `session_start()` calls

**"Column not found: order_date" Error**
- This is resolved — all queries now use `created_at` instead of `order_date`
- If it reappears, search for `order_date` and replace with `created_at`

**Images Not Loading**
- Verify image URLs are accessible
- Check file permissions in `assets/uploads/` and `assets/menu/`

**Chatbot Not Working**
- Verify Anthropic API key is set
- Check API endpoint accessibility
- Review browser console for JavaScript errors

**Admin Dashboard Shows Login Instead of Dashboard**
- You must log in at `admin/admin-login.php` first
- The session checks `$_SESSION['admin_logged_in'] === true`

---

## 🤝 Contributing

We welcome contributions! Here's how to get started:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow **PSR-12** for PHP code
- Use meaningful variable and function names
- Comment complex logic
- Sanitize all user inputs
- Use prepared statements for all database queries
- Generate / verify CSRF tokens on all forms

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 📞 Support

For support and inquiries:

- **Email**: support@hungryfood.com
- **Phone**: 0317-0544863
- **WhatsApp**: +92 317 0544863
- **Address**: MM Alam Road, Lahore, Punjab, Pakistan

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) — Frontend framework
- [Chart.js](https://www.chartjs.org/) — Data visualization
- [Font Awesome](https://fontawesome.com/) — Icons
- [Google Fonts](https://fonts.google.com/) — Typography (Poppins & Inter)
- [Unsplash](https://unsplash.com/) — Food photography
- [Anthropic](https://www.anthropic.com/) — Claude AI chatbot API
- [XAMPP](https://www.apachefriends.org/) — Local development environment

---

<p align="center">
  Made with ❤️ by the Hungry Food Team<br>
  © 2024 Hungry Food. All rights reserved.
</p>

- **iOS Zoom Prevention** — 16px font size on inputs to prevent auto-zoom
- **Print Styles** — Optimized layout for printing
- **Dark Mode** — Respects `prefers-color-scheme`
- **Reduced Motion** — Respects `prefers-reduced-motion`

### Files
- `assets/css/modern-ui.css` — Complete responsive stylesheet
- `assets/js/responsive.js` — Mobile interaction enhancements

---


## 🤖 AI Chatbot Integration

The project integrates the **Anthropic Claude API** to power "Zara", an AI assistant visible as a floating chat widget across customer-facing pages.

### How It Works
1. The chatbot UI (`assets/js/chatbot.js`) sends user messages via `fetch()` (AJAX)
2. The message hits `ai.php`, which acts as the API proxy
3. `ai.php` calls the Anthropic Claude API with a system prompt describing the restaurant
4. The AI response is parsed and returned to the browser
5. The chatbot UI renders the response with typed-message animation

### Configuration
Set your Anthropic API key via an environment variable (recommended) or directly in `ai.php`:
```php
$api_key = getenv('ANTHROPIC_API_KEY');
```

---

### AI Chatbot Configuration
Set the Anthropic API key in `ai.php`:
```php
$api_key = getenv('ANTHROPIC_API_KEY'); // via environment variable
```

---

   - Start **MySQL** module

4. **Set up the database** *(choose one option)*

   **Option A — Import SQL file**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click **Import**
   - Select the `database.sql` file from the project root
   - Click **Go** to import the complete schema + sample data

   **Option B — Auto-install script**
   - Open phpMyAdmin and create a new database named `hungry_food`
   - Navigate to: `http://localhost/hungry-food/install.php`
   - This creates all required tables and inserts sample data

5. **Access the application**
   - Frontend: `http://localhost:8080/hungry-food/`
   - Admin Panel: `http://localhost:8080/hungry-food/admin/admin-login.php`

---

| `includes/` | Reusable layout partials (header, footer, navbar) |
| Root `*.php` | Public-facing pages accessible by customers |

---

        ↓               ↓            ↓            ↓
[Table Type]      [Occasion]   [Confirm]   [Status Update]
```

---

3. Auth check       →  verify session (admin pages check admin_logged_in)
4. Business logic   →  prepare PDO statement + execute with params
5. View render      →  HTML with embedded PHP, Bootstrap, Chart.js
6. Response         →  HTML sent back to browser
```

---

| **CDN Services** | Fast asset delivery | Bootstrap, Font Awesome, Chart.js |

### Development Tools

- **XAMPP** — Local development environment (Apache + MySQL + PHP)
- **Git** — Version control
- **VS Code** — Primary IDE
- **Chrome DevTools** — Debugging and testing
- **phpMyAdmin** — Database administration

---

- **Add / Edit Items** — Create and modify menu items with categories
- **Image Upload** — Upload food images with automatic optimization
- **Price Management** — Set prices in USD with automatic PKR conversion
- **Availability Toggle** — Mark items as available / unavailable

#### 📋 Reservation & Contact Management
- **Reservation Dashboard** — View all reservations with date filtering, status updates
- **Contact Inbox** — View customer inquiries, mark as read / resolved, respond to customers
- **Offers Management** — Create and manage promotional offers and coupon codes

---

**Hungry Food** is a modern, full-stack restaurant management system designed to streamline online food ordering, table reservations, catering services, and administrative operations. Built with **PHP** and **MySQL**, it features a responsive **Bootstrap 5** UI, **AI-powered customer support** via the Anthropic Claude API, and comprehensive analytics for business insights.

### Key Highlights

- 🛒 **Complete E-Commerce Flow** — Shopping cart, checkout, order tracking
- 🤖 **AI Chatbot (Zara)** — Powered by Anthropic Claude API for customer assistance
- 📊 **Admin Dashboard** — Real-time analytics, revenue tracking with Chart.js
- 📱 **Fully Responsive** — Mobile-first design that works on all devices
- 🔒 **Enterprise Security** — CSRF protection, SQL injection prevention, XSS filtering
- 💳 **Multi-Currency Support** — USD ($) and PKR (Rs) with dynamic conversion
- 📧 **Email Notifications** — Order confirmations and status updates
- 🎨 **Modern UI/UX** — Clean design with animations and micro-interactions

---
