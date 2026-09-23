# Database Schema Documentation

## Database Name: `sarvam_real_estate`

### Table: `admins`
Stores administrator credentials.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `name` | VARCHAR(100) | NOT NULL | Admin's full name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Admin email address |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Record creation time |

### Table: `users`
Stores registered customer data.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `first_name` | VARCHAR(50) | NOT NULL | User's first name |
| `last_name` | VARCHAR(50) | NOT NULL | User's last name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | User's email |
| `phone` | VARCHAR(20) | NULL | User's phone number |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `status` | ENUM('active','inactive') | DEFAULT 'active' | Account status |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Registration time |

### Table: `agents`
Stores real estate agent details.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `name` | VARCHAR(100) | NOT NULL | Agent's full name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Agent's email |
| `phone` | VARCHAR(20) | NOT NULL | Agent's contact number |
| `agency_name` | VARCHAR(100) | NULL | Associated agency |
| `description` | TEXT | NULL | Agent bio |
| `image` | VARCHAR(255) | NULL | Path to profile picture |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Record creation time |

### Table: `locations`
Stores geographical location data.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `city` | VARCHAR(100) | NOT NULL | City name |
| `state` | VARCHAR(100) | NOT NULL | State/Province name |
| `country` | VARCHAR(100) | NOT NULL | Country name |

### Table: `property_types`
Stores categories of properties (e.g., Apartment, Villa).

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `type_name` | VARCHAR(50) | NOT NULL | Property type name |

### Table: `properties`
Core table storing property listings.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `title` | VARCHAR(255) | NOT NULL | Listing title |
| `description` | TEXT | NOT NULL | Property description |
| `price` | DECIMAL(15,2) | NOT NULL | Price in local currency |
| `area_sqft` | INT(11) | NOT NULL | Area in square feet |
| `bedrooms` | INT(11) | NOT NULL | Number of bedrooms |
| `bathrooms` | INT(11) | NOT NULL | Number of bathrooms |
| `agent_id` | INT(11) | FK (agents.id) | Agent managing the listing |
| `location_id` | INT(11) | FK (locations.id) | Property location |
| `type_id` | INT(11) | FK (property_types.id)| Property type |
| `status` | ENUM('available','sold','rented')| DEFAULT 'available' | Current status |
| `purpose` | ENUM('sale','rent') | NOT NULL | Transaction type |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Listing creation time |

### Table: `property_images`
Stores multiple images for properties.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `property_id` | INT(11) | FK (properties.id) | Associated property |
| `image_path` | VARCHAR(255) | NOT NULL | File path |
| `is_primary` | BOOLEAN | DEFAULT FALSE | Whether it is main image |

### Table: `inquiries`
Stores user inquiries about properties.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `property_id` | INT(11) | FK (properties.id) | Associated property |
| `user_id` | INT(11) | FK (users.id) | User who made inquiry |
| `message` | TEXT | NOT NULL | Inquiry content |
| `status` | ENUM('pending','read','replied')| DEFAULT 'pending' | Inquiry status |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Submission time |

### Table: `wishlist`
Stores user-saved properties.

| Column | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT(11) | PK, Auto Increment | Unique identifier |
| `user_id` | INT(11) | FK (users.id) | User saving property |
| `property_id` | INT(11) | FK (properties.id) | Property being saved |
| `added_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Time added |

## Foreign Key Relationships
- `properties.agent_id` references `agents.id`
- `properties.location_id` references `locations.id`
- `properties.type_id` references `property_types.id`
- `property_images.property_id` references `properties.id` (ON DELETE CASCADE)
- `inquiries.property_id` references `properties.id` (ON DELETE CASCADE)
- `inquiries.user_id` references `users.id` (ON DELETE CASCADE)
- `wishlist.property_id` references `properties.id` (ON DELETE CASCADE)
- `wishlist.user_id` references `users.id` (ON DELETE CASCADE)
