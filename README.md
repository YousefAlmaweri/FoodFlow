# FoodFlow — CIT6224 Web Application Development
## Group 19 | Multimedia University (MMU)

---

## 👥 Group Members

| Student ID   | Name                               | Role                           |
|--------------|------------------------------------|--------------------------------|
| 241UC240P4   | Al-Maweri Yousef Mohammed Abdullah | Lead Full-Stack Developer      |
| 241UC2400T   | Bara Samih Jamal Yousef            | Lead Systems & Security Developer |
| 241UC240T4   | Abdulmalik Babiker Fadlalmula Hussain| Backend & Operations Developer |

---

## ⚙️ Installation (XAMPP — Windows)

### Step 1 — Copy files
Copy the `assignment/` folder to:
```
C:\xampp\htdocs\assignment\
```

### Step 2 — Start XAMPP
Open **XAMPP Control Panel** → Start **Apache** and **MySQL**.

> **If MySQL crashes on start:** Go to `C:\xampp\mysql\data\` and delete `ib_logfile0` and `ib_logfile1`, then start MySQL again.

### Step 3 — Run setup
Open your browser and go to:
```
http://localhost/assignment/setup_db.php
```
This creates the database, all tables, and seeds sample data automatically.

### Step 4 — Open the site
```
http://localhost/assignment/index.php
```

---

## 🔑 Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@foodflow.com | Admin123! |
| Partner (Burger Hub) | partner@burgerhub.com | Partner123! |
| Partner (Asian Kitchen) | partner@asiankitchen.com | Partner123! |
| Customer | customer@foodflow.com | Customer123! |

---

## 📁 File Structure

```
assignment/
├── assets/
│   ├── css/style.css        Custom CSS only (no Bootstrap/Tailwind)
│   └── js/main.js           Cart, JS validation, theme, mobile nav
├── includes/
│   ├── header.php           Shared header via PHP include
│   └── footer.php           Shared footer via PHP include
├── index.php                Home — restaurant listing & search
├── about.php                Platform description
├── restaurant.php           Restaurant detail & menu page
├── auth.php                 Login & Registration
├── checkout.php             Cart checkout & order placement
├── dashboard.php            Role-based dashboard (customer/partner/admin)
├── menu.php                 Partner: full-page menu CRUD + availability toggle
├── orders.php               Customer: full order history with itemised breakdown
├── reports.php              Admin: analytics (stats, top restaurants, order breakdown)
├── profile.php              User profile management
├── team.php                 Group members page (required by rubric)
├── config.php               DB connection (auto-detects port)
├── setup_db.php             One-time DB setup script
├── foodflow_db.sql          Database export (alternative import method)
└── README.md                This file
```

---

## 🔐 Security

| Threat | Fix |
|--------|-----|
| SQL Injection | PDO prepared statements throughout |
| XSS | `htmlspecialchars()` on all output |
| Session Fixation | `session_regenerate_id(true)` on login |
| Password Storage | `password_hash(PASSWORD_DEFAULT)` bcrypt |
| Unauthorised Access | Role-based redirects on every protected page |
| Price Tampering | Server-side price re-verification from DB at checkout |
