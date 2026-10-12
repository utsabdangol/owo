# OWO — E-Commerce Web Application

A complete, full-stack e-commerce application for the **OWO** clothing brand, built with PHP, MySQL, and Khalti payment gateway. Designed to run on XAMPP localhost.

---

## Quick Start

### 1. Prerequisites
- **XAMPP** (Apache + MySQL) — [xampp.apache.org](https://www.apachefriends.org/)
- PHP 8.0+ (included with XAMPP)
- A browser

### 2. Place Files
Clone or copy this project folder to:
```
C:\xampp\htdocs\owo\
```

### 3. Create the Database

**Option A — phpMyAdmin (recommended):**
1. Open `http://localhost/phpmyadmin`
2. Click **Import** → choose `owo_database.sql`
3. Click **Go**

**Option B — Command line:**
```bash
mysql -u root -p < C:\xampp\htdocs\owo\owo_database.sql
```

### 4. Configure the App

Open `config/config.php` and verify:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'owo_store');
define('DB_USER', 'root');
define('DB_PASS', '');          // Your MySQL password (blank by default in XAMPP)
define('APP_URL', 'http://localhost/owo');
```

### 5. Add Khalti Sandbox Keys

In `config/config.php`, replace the placeholder values:

```php
define('KHALTI_PUBLIC_KEY',  'test_public_key_YOUR_KEY_HERE');
define('KHALTI_SECRET_KEY',  'test_secret_key_YOUR_KEY_HERE');
```

Get your **Sandbox** keys from: [khalti.com/join/merchant](https://khalti.com/join/merchant/)

> **Note:** To go live, replace the sandbox keys with live keys and change `KHALTI_BASE_URL` to `https://khalti.com/api/v2/`

### 6. Create Upload Directory

The app auto-creates this, but you can do it manually:
```
C:\xampp\htdocs\owo\uploads\products\
```
Ensure Apache has write permissions to this folder.

### 7. Run

1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Visit: `http://localhost/owo/`
3. Admin panel: `http://localhost/owo/admin/`

---

## Default Admin Credentials

| Username | Password  |
|----------|-----------|
| `admin`  | `admin123` |

> ⚠️ **Change the admin password immediately after first login!**
> Run in phpMyAdmin:
> ```sql
> UPDATE admins SET password_hash = '$2y$12$YOUR_NEW_HASH' WHERE username = 'admin';
> ```
> Generate a hash with: `echo password_hash('your_new_password', PASSWORD_BCRYPT);`

---

## Project Structure

```
owo/
├── config/
│   └── config.php              # DB credentials + Khalti keys
├── includes/
│   ├── auth.php                # Session auth helpers (customer + admin)
│   ├── cart.php                # Session-based cart helpers
│   ├── db.php                  # PDO database connection
│   ├── helpers.php             # Utility functions
│   └── khalti.php              # Khalti API integration
├── partials/
│   ├── header.php              # Shared navbar header
│   └── footer.php              # Shared footer
├── admin/
│   ├── partials/
│   │   ├── layout-header.php   # Admin sidebar layout (open)
│   │   └── layout-footer.php   # Admin layout (close)
│   ├── login.php               # Admin login
│   ├── logout.php
│   ├── index.php               # Dashboard
│   ├── products.php            # Product list
│   ├── product-add.php         # Add product
│   ├── product-edit.php        # Edit product
│   ├── orders.php              # Order list
│   └── order-detail.php        # Order detail + status update
├── assets/
│   ├── css/
│   │   ├── style.css           # Customer-facing styles
│   │   └── admin.css           # Admin panel styles
│   ├── js/
│   │   └── main.js             # Frontend JS
│   └── img/
│       └── placeholder.php     # SVG placeholder image generator
├── uploads/
│   └── products/               # Uploaded product images (auto-created)
├── index.php                   # Homepage
├── shop.php                    # Shop / all products
├── product.php                 # Product detail + add to cart
├── cart.php                    # Cart management
├── checkout.php                # Checkout form + Khalti initiation
├── payment-verify.php          # Khalti callback verification
├── order-confirmation.php      # Post-payment confirmation
├── login.php                   # Customer login
├── register.php                # Customer registration
├── logout.php                  # Customer logout
├── orders.php                  # Customer order history
├── order-detail.php            # Customer order detail
└── owo_database.sql            # Database schema + seed data
```

---

## Khalti Payment Flow

```
1. Customer fills checkout form
2. PHP creates a pending order in DB
3. initiateKhaltiPayment() calls Khalti API → returns payment_url
4. Customer redirected to Khalti checkout page
5. Customer pays (sandbox: use test credentials)
6. Khalti redirects to /payment-verify.php?pidx=...
7. verifyKhaltiPayment() calls Khalti lookup API
8. If status = "Completed" → order marked PAID → cart cleared
9. Customer sees order confirmation page
```

### Khalti Sandbox Test Credentials
Use these on the Khalti payment page during testing:
- **Mobile:** `9800000000` / `9800000001`
- **MPIN:** `1111`
- **OTP:** `987654`

---

## Database Schema

| Table         | Purpose                           |
|---------------|-----------------------------------|
| `users`       | Customer accounts                 |
| `admins`      | Admin accounts (separate auth)    |
| `products`    | Product catalog with soft delete  |
| `orders`      | Orders with Khalti transaction ID |
| `order_items` | Line items per order              |

---

## Features

### Customer Side
- ✅ Homepage with hero, featured products, marquee banner
- ✅ Shop page with category filter, sort, search, pagination
- ✅ Product detail with size selector, add to cart
- ✅ Cart with quantity update and remove
- ✅ Checkout → Khalti payment → Order confirmation
- ✅ Register / Login / Logout (session-based)
- ✅ Order history & order detail for logged-in users
- ✅ Guest checkout (no account required)

### Admin Panel
- ✅ Dashboard: revenue, orders, products, customer stats + low stock alerts
- ✅ Product CRUD: add, edit, delete (soft), image upload
- ✅ Orders: list with status filter tabs, view detail
- ✅ Update order status: pending → paid → shipped → delivered
- ✅ Separate admin session (not shared with customers)

---

## Going to Production

1. Set `APP_URL` to your live domain
2. Swap Khalti sandbox keys → live keys
3. Change `KHALTI_BASE_URL` to `https://khalti.com/api/v2/`
4. Set a strong MySQL password and update `config.php`
5. Enable HTTPS on your server
6. Set `display_errors = Off` in php.ini

---
Viewed khalti.php:105-133
Viewed khalti.php:1-60
Viewed config.php:1-33

To configure your Khalti Sandbox integration, you need to add your test keys to the main configuration file. 

You can find the settings in this file:
`c:\xampp\htdocs\owo\config\config.php`

Open that file and look for lines **17 and 18**:
```php
// ─── Khalti Payment Gateway (Sandbox) ────────────────────────────────────────
// Replace with live keys when going to production
define('KHALTI_PUBLIC_KEY',  'test_public_key_YOUR_KHALTI_PUBLIC_KEY_HERE');
define('KHALTI_SECRET_KEY',  'test_secret_key_YOUR_KHALTI_SECRET_KEY_HERE');
```

Replace `'test_public_key_YOUR_KHALTI_PUBLIC_KEY_HERE'` and `'test_secret_key_YOUR_KHALTI_SECRET_KEY_HERE'` with the actual test keys provided in your Khalti Merchant Dashboard under the "Test/Sandbox Keys" section.

The `KHALTI_BASE_URL` is already correctly set to the sandbox endpoint (`https://a.khalti.com/api/v2/`).
*OWO E-Commerce — Built with PHP, MySQL, and Khalti. Academic + real-world project.*
