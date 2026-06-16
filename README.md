# GalateArt: Platform for Local Digital Artists

## Overview

GalateArt is a web-based application designed to facilitate local digital artists in Indonesia. The platform allows artists to showcase their artworks, receive commission orders, and connect with their audience. It integrates a localized digital payment system to simplify transactions. The system enables users to browse, like, save, and purchase artworks or request custom commissions directly from artists.

The application supports post management, commission tracking, user interactions (likes/comments), cart management, notifications, and an administrative dashboard for moderating content and managing users.

**Note:** This project is for educational/testing purposes only. Do not use real payment details if deployed.

---

## Team

| Name | Student ID | Role |
|--------|------------|--------|
| Abel Priya Devara | F1D02410032 | Fullstack Developer |
| Muhammad Ridho Kurniawan | F1D02410079 | Fullstack Developer |
| Ridho Hidayat | F1D02410089 | Fullstack Developer |

---

## Key Features

### Regular Users
- Browse and search for artworks and artists.
- Like, comment, and save posts to collections.
- Follow favorite artists.
- Add artworks to the cart and perform mock checkouts.
- Order custom commissions from artists.
- Chat with other users and artists.
- Receive real-time notifications for order updates.

### Artists
- Access all Regular User features.
- Upload and manage artworks (set prices, free downloads, or display-only).
- Manage commission status (open/close).
- Define commission tiers and options.
- Accept or reject incoming commission requests.
- Track followers and engagement on their posts.

### Administrators
- Access a centralized dashboard with statistics.
- View and manage incoming reports regarding users or posts.
- Approve or reject reports (automatically hiding posts or banning users).
- Manage user accounts and view activity logs.
- Monitor overall platform activity.

---

## Workflow

### Purchasing an Artwork
1. A regular user browses the gallery and selects an artwork.
2. The user adds the artwork to their cart.
3. The user proceeds to checkout via the local payment gateway simulation.
4. Upon successful payment, the user can download the original source file.

### Commissioning an Artist
1. A user visits an artist's profile and clicks "Order Komisi".
2. The user selects a tier, add-ons, and provides references/details.
3. The artist receives a notification of the pending commission.
4. The artist reviews and either accepts or rejects the commission.
5. If accepted, the user completes the payment, and the artist begins work.
6. Communication continues via the built-in chat system.

---

## Technology Stack

### Backend
- PHP Native
- PHP 8+

### Frontend
- HTML5
- CSS3 (Vanilla, CSS Grid/Flexbox, Glassmorphism)
- JavaScript (Vanilla, Fetch API)

### Database
- MySQL / MariaDB

### Additional Tools
- FontAwesome (Icons)
- Google Fonts (Poppins)
- Google OAuth (Sign-in)

---

## Project Structure

```text
GalateArt/
│
├── api/                   # Backend endpoints (RESTful)
│   ├── admin.php          # Admin dashboard API
│   ├── auth.php           # Authentication & session API
│   ├── comments.php       # Comment management
│   ├── like.php           # Like interactions
│   └── ...                # Other endpoints
│
├── components/            # Reusable UI parts
│   ├── navbar.php         # Main navigation bar
│   ├── bootstrap.php      # Session & helper functions
│   ├── art-modal.php      # Fullscreen art viewer
│   └── auth-modals.php    # Login & register modals
│
├── config/                # Database and configuration
│   ├── Db.php             # Database connection wrapper
│   └── setup.php          # Initial database schema setup
│
├── js/                    # Frontend JavaScript
│   ├── auth.js            # Async authentication logic
│   ├── art-modal.js       # Dynamic modal and interactions
│   ├── navbar.js          # Notifications and search
│   └── ...                # Other scripts
│
├── Assets/                # Static images and uploads
│
├── index.php / landing.php# Main gallery page
├── admin.php              # Administrator dashboard
├── profile.php            # User profile page
├── artist-profile.php     # Artist portfolio page
├── commission.php         # Commission ordering system
├── cart.php               # Shopping cart
└── style.css              # Global stylesheet
```

---

## Database Schema (Overview)

- **users**: Stores regular users, artists, and admin accounts (roles, ban status).
- **posts**: Stores uploaded artworks, prices, and status.
- **artist_profiles**: Extended details for artists (portfolio, commission status).
- **comments**: Comments made on posts.
- **follows**: Follower-following relationships.
- **likes / saves**: User interaction tracking on posts.
- **cart**: Shopping cart items.
- **commissions**: Custom artwork orders and their statuses.
- **reports**: Violations reported by users, handled by admins.
- **notifications**: System notifications for users.

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
```

### 2. Configure Database Connection

Open:

```php
config/Db.php
```

Ensure your database credentials match your local server environment:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "galateart"; // Or your preferred DB name
```

### 3. Run Database Setup

Navigate to your browser to automatically create the database and tables:

```text
http://localhost/GalateArt/config/setup.php
```

### 4. Enable Google Sign-in (Optional)
Ensure you have configured a valid Google OAuth Client ID in `js/auth.js` and matching redirect URIs in Google Cloud Console.

### 5. Move the Project

Place the project folder inside your local web server document root:

```text
xampp/htdocs/GalateArt
```

### 6. Start Services

Start Apache and MySQL using the XAMPP Control Panel.

### 7. Access the Application

Open your browser and navigate to:

```text
http://localhost/GalateArt/landing.php
```

---

## User Roles

| Role | Description |
|--------|-------------|
| Administrator | Moderates platform, handles reports, and manages accounts. |
| Artist | Uploads artworks, accepts commissions, and manages their portfolio. |
| Regular User | Browses art, interacts (likes/comments), purchases items, and orders commissions. |
