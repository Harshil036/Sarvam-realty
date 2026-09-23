# Software Requirements Specification (SRS)

## 1. Introduction
### 1.1 Purpose
The purpose of this document is to define the requirements for the Sarvam Real Estate platform. This system facilitates property listing, searching, and management for buyers, renters, owners, and real estate agents.

### 1.2 Scope
Sarvam Real Estate is a web-based application built with Core PHP and MySQL. It includes a user-facing website for browsing properties and making inquiries, a user dashboard for managing preferences, and a comprehensive admin panel for system administration.

### 1.3 Definitions
- **User**: Any individual interacting with the frontend of the website (Buyer, Renter, Owner).
- **Agent**: A professional representing properties and interacting with clients.
- **Admin**: System administrator with full access to the backend management panel.

## 2. Overall Description
### 2.1 Product Perspective
The system is an independent, web-based application following a client-server model. It is designed to run on a standard LAMP/XAMPP stack.

### 2.2 Functions
- Property Search and Filtering
- User Registration and Profile Management
- Property Listing and Management
- Inquiry and Lead Management
- Wishlist functionality

### 2.3 User Classes
- **Guest Users**: Can browse properties, view details, and search.
- **Registered Users**: Can save properties to wishlist, submit inquiries, and manage profiles.
- **Administrators**: Manage users, properties, content, and system settings.

## 3. Specific Requirements
### 3.1 Functional Requirements
- **FR1 (Authentication)**: The system shall allow users to register, log in, and reset passwords securely.
- **FR2 (Property Search)**: Users shall be able to search for properties by location, type, price range, and amenities.
- **FR3 (Property Details)**: The system shall display comprehensive property details including images, price, area, description, and agent contact info.
- **FR4 (Inquiries)**: Logged-in users shall be able to send inquiries regarding specific properties.
- **FR5 (Wishlist)**: Users shall be able to add/remove properties to/from a personal wishlist.
- **FR6 (Admin - Dashboard)**: Admins shall view key metrics (total properties, active users, pending inquiries).
- **FR7 (Admin - Property Management)**: Admins shall be able to create, read, update, and delete property listings.
- **FR8 (Admin - User Management)**: Admins shall manage registered users and agents.

### 3.2 Non-Functional Requirements
- **Performance**: Pages should load in under 3 seconds under normal load.
- **Security**: Passwords must be hashed. All database queries must use prepared statements to prevent SQL injection.
- **Usability**: The application must be fully responsive and accessible on mobile, tablet, and desktop devices.
- **Availability**: The system targets 99.9% uptime in a production environment.

### 3.3 Database Requirements
- Relational database management system (MySQL).
- Properly normalized tables to reduce data redundancy.

### 3.4 Interface Requirements
- HTML5/CSS3 compliant frontend.
- Integration with modern browsers (Chrome, Firefox, Safari, Edge).

## 4. System Architecture
The application follows an MVC-like (Model-View-Controller) structure customized for Core PHP.
- **Views**: HTML/CSS/PHP files rendering the user interface.
- **Models/Core**: PHP classes handling database interactions and business logic.
- **Controllers**: PHP scripts processing form submissions and routing requests.

## 5. Use Case Diagrams
```text
[Guest User]
    |--> Browse Properties
    |--> Search Properties
    |--> Register/Login

[Registered User]
    |--> (All Guest User actions)
    |--> Manage Profile
    |--> Add to Wishlist
    |--> Submit Inquiry

[Admin]
    |--> Login to Admin Panel
    |--> Manage Properties
    |--> Manage Users/Agents
    |--> View Inquiries
    |--> Configure Settings
```
