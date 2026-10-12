# OWO E-Commerce Project Report

## TABLE OF CONTENT

**CHAPTER 1** ................................................................................................................................. 1
INTRODUCTION ........................................................................................................................ 1
1.1 Introduction .............................................................................................................................. 1
1.2 Objectives ................................................................................................................................. 2
1.4 Scopes ....................................................................................................................................... 2

**CHAPTER 2** ................................................................................................................................. 5
System Design And Architecture ................................................................................................ 5
2.1 System Architecture ................................................................................................................. 5
2.1.1 Payment Gateway Integration: ............................................................................................... 6
2.2 Functional Modules ................................................................................................................... 6
2.3 Database Design ....................................................................................................................... 7
2.3.1 Table Overview ..................................................................................................................... 8
2.4 Interface Design (UI Interface/ Interface Structure Diagrams) .............................................. 11

**CHAPTER 3** ............................................................................................................................... 12
IMPLEMENTATION, TESTING AND EVALUATION ...................................................... 12
3.1 Implementation ....................................................................................................................... 12
3.1.1 Tools Used (CASE tools, Programming Languages, Database Platforms) ......................... 12
3.1.1.1 Front End Tools ...................................................................................................... 12
3.1.1.2 Backend Tools ........................................................................................................... 12
3.1.2 Hosting Environment .......................................................................................................... 13
3.1.3 Code Snippet ....................................................................................................................... 13
3.2 Payment Integration ............................................................................................................... 14
3.2.1 Gateway Provider Used: ...................................................................................................... 14
3.2.2 API Authentication Method: ............................................................................................... 14
3.2.3 Sample JSON Request/Response: ....................................................................................... 14
3.2.4 Error Handling and Confirmation: ...................................................................................... 15
3.3 Testing .................................................................................................................................... 16
3.3.1 Unit Testing ......................................................................................................................... 16
4.2.2 System testing...................................................................................................................... 18
3.4 User Feedback ........................................................................................................................ 23
3.4.1 Summary of Test User Responses ....................................................................................... 23
3.5 Limitations and Future Scope ................................................................................................. 24
3.5.1 Unimplemented Features: .................................................................................................... 24
3.5.2 Future Upgrades: ................................................................................................................. 24
3.6 Conclusion .............................................................................................................................. 25
3.6.1 System Impact Summary: ................................................................................................... 25
3.6.2 MIS Takeaways (Decision Support) ................................................................................... 25

---

# CHAPTER 1
## INTRODUCTION

### 1.1 Introduction
The OWO E-Commerce project is a comprehensive online retail platform designed to provide a seamless shopping experience for clothing and apparel. The system allows users to browse products, manage their shopping carts, and securely checkout using integrated digital payment gateways. The project utilizes a modern web stack tailored for fast, secure, and user-friendly interaction.

### 1.2 Objectives
* To develop a robust e-commerce platform for listing and selling products.
* To provide an intuitive user interface for customers to browse, add to cart, and purchase items.
* To integrate a secure and reliable digital payment gateway (Khalti) for processing transactions.
* To implement an administrative backend for managing products, categories, users, and orders.

### 1.4 Scopes
The scope of this project encompasses:
* **Customer-facing Application:** Registration, authentication, product catalog, cart management, and checkout.
* **Admin Dashboard:** Order management, inventory tracking, and data persistence.
* **Payment Processing:** Real-time transaction verification via Khalti API.
* **Security:** Secure password hashing (bcrypt) and prepared statements (PDO) to prevent SQL injection.

---

# CHAPTER 2
## System Design And Architecture

### 2.1 System Architecture
The application follows a standard **Client-Server Architecture** utilizing the LAMP/XAMPP stack.
* **Client Side:** Renders the user interface using HTML, CSS, and JavaScript. Communicates with the server via HTTP GET/POST requests.
* **Server Side:** PHP scripts handle business logic, process forms, interact with the database, and communicate with external APIs (Khalti).
* **Database:** MySQL database stores all persistent application data, ensuring relational integrity.

#### 2.1.1 Payment Gateway Integration:
The system uses the Khalti Payment Gateway. The architecture involves:
1. **Initiation:** The checkout process constructs a payload and calls the Khalti API to initiate a payment.
2. **Redirection:** The user is redirected to Khalti's secure payment portal.
3. **Callback/Verification:** Khalti redirects back to `payment-verify.php` with a `pidx` token, which the server uses to confirm the payment status via a server-to-server API call.

### 2.2 Functional Modules
The file structure of the project organizes these modules:
* `index.php` / `shop.php` / `product.php`: Product catalog and display modules.
* `login.php` / `register.php` / `logout.php`: User authentication modules.
* `cart.php` / `checkout.php`: Cart management and order placement modules.
* `payment-verify.php` / `order-confirmation.php`: Payment processing and receipt generation.
* `admin/`: Backend administrative modules.
* `includes/` / `partials/` / `config/`: Reusable components, database connections, and helper functions.

### 2.3 Database Design
The database (`owo_store`) is built on MySQL using the InnoDB engine for transaction support and relational integrity.

#### 2.3.1 Table Overview
* **`users`**: Stores customer information (`id`, `name`, `email`, `password_hash`, `phone`).
* **`admins`**: Stores administrative credentials (`id`, `username`, `password_hash`).
* **`products`**: Maintains inventory details (`id`, `name`, `description`, `price`, `stock`, `category`, `sizes`, `image_path`).
* **`orders`**: Tracks customer orders (`id`, `user_id`, `guest_info`, `shipping_address`, `total_amount`, `khalti_pidx`, `status`).
* **`order_items`**: Maps individual products to their respective orders (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`).

### 2.4 Interface Design (UI Interface/ Interface Structure Diagrams)
The UI is designed to be responsive and accessible.
* **Header/Navbar:** Contains branding, navigation links, and a cart icon with an item counter.
* **Product Grid:** Displays products with images, titles, and prices.
* **Checkout Flow:** A multi-step form collecting shipping details followed by a "Pay with Khalti" action button.
* **Admin View:** Data tables for managing the catalog and reviewing order statuses.

---

# CHAPTER 3
## IMPLEMENTATION, TESTING AND EVALUATION

### 3.1 Implementation

#### 3.1.1 Tools Used (CASE tools, Programming Languages, Database Platforms)
**3.1.1.1 Front End Tools**
* **HTML5:** For page structure and semantics.
* **CSS3:** For styling, layout, and responsive design (Custom CSS/Frameworks).
* **JavaScript:** For client-side interactivity and form validation.

**3.1.1.2 Backend Tools**
* **PHP:** Core server-side scripting language used to handle routing, sessions, and business logic.
* **MySQL:** Relational database management system.
* **PDO (PHP Data Objects):** Used for secure database interaction.

#### 3.1.2 Hosting Environment
The system is developed and tested on a local environment using **XAMPP** (Apache HTTP Server, MariaDB/MySQL, and PHP) on a Windows OS.

#### 3.1.3 Code Snippet
*Example: Secure Database Query & Khalti Verification (`payment-verify.php`)*
```php
// Load the order using Prepared Statements to prevent SQL Injection
$orderStmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND khalti_pidx = :pidx");
$orderStmt->execute([':id' => $orderId, ':pidx' => $pidx]);
$order = $orderStmt->fetch();

if ($verification['success']) {
    // Mark order as paid
    $db->prepare("UPDATE orders SET status = 'paid', khalti_transaction_id = :txid WHERE id = :id")
       ->execute([':txid' => $verification['transaction_id'], ':id' => $orderId]);
    cartClear();
}
```

### 3.2 Payment Integration

#### 3.2.1 Gateway Provider Used:
**Khalti** - A prominent digital wallet and payment gateway in Nepal.

#### 3.2.2 API Authentication Method:
The Khalti API is authenticated using a **Secret Key** provided via the HTTP `Authorization` header (`Key <Live/Test_Secret_Key>`).

#### 3.2.3 Sample JSON Request/Response:
**Request to Initiate Payment:**
```json
{
  "return_url": "http://localhost/owo/payment-verify.php",
  "website_url": "http://localhost/owo",
  "amount": 1000,
  "purchase_order_id": "Order_123",
  "purchase_order_name": "OWO Store Purchase",
  "customer_info": {
    "name": "Test User",
    "email": "test@example.com",
    "phone": "9800000000"
  }
}
```
**Response from Gateway:**
```json
{
  "pidx": "HT6o6CAjwaLGoCkwRj98hB",
  "payment_url": "https://test-pay.khalti.com/?pidx=HT6o6...",
  "expires_at": "2026-07-11T12:00:00.000000Z",
  "expires_in": 1800
}
```

#### 3.2.4 Error Handling and Confirmation:
If payment fails or the user cancels, Khalti redirects with an error status. The system catches this, prevents the order from being marked as 'paid', displays an error message using the session flash messaging system, and redirects the user back to `checkout.php`.

### 3.3 Testing

#### 3.3.1 Unit Testing
Individual modules such as database connections, cart addition (`cart.php`), and session management were tested independently to ensure isolated functions performed as expected.

#### 4.2.2 System testing
End-to-end integration testing was conducted covering:
1. User Registration -> Login -> Add Product to Cart -> Checkout.
2. Checkout -> Redirection to Khalti -> Payment Completion -> Verification Callback.
3. Order confirmation page display and database status update validation.

### 3.4 User Feedback

#### 3.4.1 Summary of Test User Responses
Initial test users reported that the UI was clean and navigation was intuitive. The payment flow redirect was smooth. Some feedback indicated a desire for more product filtering options and order tracking capabilities.

### 3.5 Limitations and Future Scope

#### 3.5.1 Unimplemented Features:
* Advanced product filtering (by color, size, price range).
* User wishlists.
* Automated email/SMS order confirmations.

#### 3.5.2 Future Upgrades:
* Integration of additional payment gateways (eSewa, Fonepay, IME Pay).
* Development of a dedicated mobile application (React Native/Flutter).
* Advanced analytics dashboard for administrative MIS reporting.

### 3.6 Conclusion

#### 3.6.1 System Impact Summary:
The implementation of the OWO E-Commerce platform successfully digitalizes the retail process for the brand, enabling secure, fast, and 24/7 sales operations.

#### 3.6.2 MIS Takeaways (Decision Support)
The administrative backend and database structures lay the foundation for a robust Management Information System (MIS). Data gathered from `orders` and `order_items` tables can be leveraged for inventory forecasting, customer behavior analysis, and targeted marketing campaigns in the future.
