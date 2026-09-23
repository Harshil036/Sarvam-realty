# Sarvam Real Estate

**Find Your Dream Property with Confidence.**

Sarvam Real Estate is a comprehensive, production-quality web platform for property listing, searching, and management. It connects property buyers, sellers, renters, and real estate agents in a unified digital ecosystem. Built with a modern, professional dark/teal theme, it features complete user dashboard tools and a robust admin panel.

---

## Key Features

### User Panel
- **Responsive Landing Page**: Full-viewport hero area with search filters, featured property listings, property type category grids, agent profiles, and client testimonial sliders.
- **Advanced Search & Filtering**: Fast, dynamic properties filter page (buy/rent types, location, bedrooms/BHK, min/max price range, and sorting options).
- **Interactive Details Page**: Property photo gallery, structured spec cards (area, beds, baths, parking, furnishing), amenities lists, wishlist saving, and inquiry contact forms.
- **User Dashboard**: Overview stats, saved properties (wishlist), and inquiry communication history table.
- **Profile Customization**: Dynamic avatar uploads, personal details form, and secure password updates.

### Admin Panel (Access via `/admin/`)
- **Control Dashboard**: Stat card grids (properties, users, agents, inquiries) and dynamic canvas-based monthly inquiry bar charts.
- **Property Management**: Full CRUD operations for listings, active status toggle, featured/premium flags, and multiple image upload.
- **User & Agent Management**: Platform-wide user suspension control and complete real estate agent profiles setup.
- **Inquiry Management**: Interactive table to update inquiry status (Pending, Responded, Closed) and view contact messages.
- **Locations & Property Types**: Dynamic platform configuration tables for active lookup tables.

---

## Technology Stack & Guidelines

- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Bootstrap 5.3, Bootstrap Icons
- **Backend**: Core PHP (No external frameworks)
- **Database**: MySQL / MariaDB (using secure PDO prepared statements)
- **Design System Palette**:
  - Primary Background / Headings: `#2D343D` (Deep Slate)
  - Secondary Background / Body Text: `#465461` (Slate Grey)
  - Accent / Icons / Links: `#729CA2` (Teal)
  - Accent Borders: `#C4DCDF` (Mist)
  - Secondary Light Page Background: `#ECF3F4` (Ice)
  - Action CTA Highlight / Badges: `#FF893B` (Orange)
  - Button Hover: `#E9782C` (Deep Orange)

---

## Installation & Setup on XAMPP

1. **Clone/Copy Project**: Copy the `Sarvam-Real-Estate` folder into your XAMPP installation directory at `C:\xampp\htdocs\`.
2. **Start Services**: Open the XAMPP Control Panel and start **Apache** and **MySQL** services.
3. **Create Database**:
   - Access phpMyAdmin at `http://localhost/phpmyadmin/`.
   - Create a new database named `sarvam_real_estate` with collation `utf8mb4_general_ci`.
4. **Import Database Dump**:
   - Select the newly created `sarvam_real_estate` database.
   - Go to the **Import** tab.
   - Choose the file located at `database/sarvam_real_estate.sql` and click **Import/Go**.
5. **Run the Project**: Open your web browser and navigate to `http://localhost/Sarvam-Real-Estate/`.

---

## Default Credentials

### Admin Panel
- **Link**: `http://localhost/Sarvam-Real-Estate/admin/`
- **Email:** `admin@sarvam.com`
- **Password:** `Admin@123`

### Demo User
- **Link**: `http://localhost/Sarvam-Real-Estate/login.php`
- **Email:** `john@example.com`
- **Password:** `User@123`

---

## Folder Structure

```text
Sarvam-Real-Estate/
├── admin/               # Admin panel dashboard and CRUD pages
│   └── includes/        # Admin header, footer, and sidebar components
├── assets/              # Static frontend assets
│   ├── css/             # style.css (Unified custom theme) & admin.css
│   ├── js/              # main.js (User-side logic) & admin.js
│   └── uploads/         # User uploaded photos (properties, agents, users)
├── config/              # Core configuration files
│   └── db.php           # PDO connection & session setup
├── database/            # Database schema exports
│   └── sarvam_real_estate.sql
├── docs/                # Project documentation & diagrams
│   ├── SRS.md           # Software Requirements Specification
│   ├── DB_Schema.md     # Relational database layout
│   └── ER_Diagram.md    # Mermaid ER Model representation
├── includes/            # Reusable UI includes (header, footer, navbar)
│   └── functions.php    # Shared helper functions
├── index.php            # Platform landing page
├── properties.php       # Property search & filtering grid
├── property-detail.php  # Property detailed specs
├── login.php            # User authentication form
├── register.php         # User sign-up form
├── forgot-password.php  # User account recovery
├── dashboard.php        # User dashboard page
├── wishlist.php         # User saved properties
├── wishlist-action.php  # AJAX endpoint for wishlist updates
├── profile.php          # User profile settings
├── my-inquiries.php     # User inquiry communications history
├── about.php            # Dynamic brand info and active agents
├── contact.php          # Contact details & inquiry form
└── README.md            # Installation & setup guide
```

---

## E2E Testing & Quality Gates

The project comes with built-in PowerShell and PHP quality verification files to check database and flow health:
- `test_flows.ps1` — Runs 23 end-to-end integration checks verifying guest protection redirects, user/admin authentication flows, AJAX wishlist toggle, and page load error filters.
- `verify_all.php` — Validates 151 items (syntax, file paths, database count schema, configuration limits, and style colors).

---

## Troubleshooting

- **Database Connection Error**: Verify MariaDB/MySQL is running on port 3306. Check database credentials in `config/db.php`.
- **Session Locking / Hangs**: The project uses optimized stateless HTTP connections. Ensure browser cookies are enabled to track `PHPSESSID`.
- **File Upload Issues**: Ensure the subdirectories in `assets/uploads/` are writable (`chmod 755` on Unix systems, already pre-configured for Windows/XAMPP).
