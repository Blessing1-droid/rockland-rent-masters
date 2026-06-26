-- ==========================================================
-- ROCKLAND RENT MASTERS V2
-- Kenya First • Global Ready
-- MySQL 8+
-- ==========================================================

CREATE DATABASE IF NOT EXISTS rockland_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rockland_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS analytics_events;
DROP TABLE IF EXISTS mpesa_transactions;
DROP TABLE IF EXISTS oauth_accounts;
DROP TABLE IF EXISTS tenant_screenings;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS property_verifications;
DROP TABLE IF EXISTS property_comparisons;
DROP TABLE IF EXISTS recently_viewed;
DROP TABLE IF EXISTS saved_properties;
DROP TABLE IF EXISTS property_views;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS property_documents;
DROP TABLE IF EXISTS property_floorplans;
DROP TABLE IF EXISTS property_videos;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS agent_profiles;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- USERS
-- ==========================================================

CREATE TABLE users (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,

email VARCHAR(191) NOT NULL UNIQUE,
password VARCHAR(255) NULL,

role ENUM(
    'tenant',
    'landlord',
    'agent',
    'admin'
) NOT NULL DEFAULT 'tenant',

phone VARCHAR(20) NULL,

avatar VARCHAR(255) NULL,

email_verified TINYINT(1) DEFAULT 0,

remember_token VARCHAR(255) NULL,

status ENUM(
    'active',
    'pending',
    'suspended'
) DEFAULT 'active',

last_login TIMESTAMP NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

);

-- ==========================================================
-- AGENT PROFILES
-- ==========================================================

CREATE TABLE agent_profiles (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

company_name VARCHAR(255) NULL,

bio TEXT NULL,

license_number VARCHAR(150) NULL,

years_experience INT DEFAULT 0,

whatsapp VARCHAR(30) NULL,

website VARCHAR(255) NULL,

rating DECIMAL(3,2) DEFAULT 0,

total_reviews INT DEFAULT 0,

verified TINYINT(1) DEFAULT 0,

FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PROPERTIES
-- ==========================================================

CREATE TABLE properties (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
owner_id BIGINT UNSIGNED NOT NULL,

agent_id BIGINT UNSIGNED NULL,

title VARCHAR(255) NOT NULL,

slug VARCHAR(255) NULL,

description LONGTEXT NOT NULL,

listing_type ENUM(
    'rental',
    'sale',
    'commercial',
    'land'
) NOT NULL,

property_type VARCHAR(100) NOT NULL,

price DECIMAL(15,2) NOT NULL,

bedrooms INT DEFAULT 0,

bathrooms INT DEFAULT 0,

parking_spaces INT DEFAULT 0,

area_size DECIMAL(10,2) NULL,

address VARCHAR(255) NOT NULL,

city VARCHAR(120) NOT NULL,

county VARCHAR(120) NOT NULL,

country VARCHAR(120) DEFAULT 'Kenya',

latitude DECIMAL(10,7) NULL,

longitude DECIMAL(10,7) NULL,

featured TINYINT(1) DEFAULT 0,

verified TINYINT(1) DEFAULT 0,

furnished TINYINT(1) DEFAULT 0,

pets_allowed TINYINT(1) DEFAULT 0,

virtual_tour_url VARCHAR(255) NULL,

video_url VARCHAR(255) NULL,

status ENUM(
    'draft',
    'active',
    'sold',
    'rented',
    'inactive'
) DEFAULT 'active',

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

INDEX(city),
INDEX(county),
INDEX(price),
INDEX(listing_type),

FOREIGN KEY (owner_id)
REFERENCES users(id)
ON DELETE CASCADE,

FOREIGN KEY (agent_id)
REFERENCES users(id)
ON DELETE SET NULL
```

);

-- ==========================================================
-- PROPERTY IMAGES
-- ==========================================================

CREATE TABLE property_images (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

image_path VARCHAR(255) NOT NULL,

is_cover TINYINT(1) DEFAULT 0,

sort_order INT DEFAULT 0,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PROPERTY VIDEOS
-- ==========================================================

CREATE TABLE property_videos (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

video_path VARCHAR(255) NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- FLOORPLANS
-- ==========================================================

CREATE TABLE property_floorplans (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

file_path VARCHAR(255) NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- DOCUMENTS
-- ==========================================================

CREATE TABLE property_documents (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

document_type VARCHAR(100),

file_path VARCHAR(255),

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY (property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- SAVED PROPERTIES
-- ==========================================================

CREATE TABLE saved_properties (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

property_id BIGINT UNSIGNED NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

UNIQUE(user_id, property_id),

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- RECENTLY VIEWED
-- ==========================================================

CREATE TABLE recently_viewed (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

property_id BIGINT UNSIGNED NOT NULL,

viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PROPERTY COMPARISONS
-- ==========================================================

CREATE TABLE property_comparisons (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

property_id BIGINT UNSIGNED NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PROPERTY VIEWS
-- ==========================================================

CREATE TABLE property_views (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

user_id BIGINT UNSIGNED NULL,

ip_address VARCHAR(100),

viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PROPERTY VERIFICATION
-- ==========================================================

CREATE TABLE property_verifications (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

verified_by BIGINT UNSIGNED NULL,

verification_type VARCHAR(100),

status ENUM(
    'pending',
    'approved',
    'rejected'
) DEFAULT 'pending',

notes TEXT NULL,

verified_at TIMESTAMP NULL,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- INQUIRIES
-- ==========================================================

CREATE TABLE inquiries (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

user_id BIGINT UNSIGNED NOT NULL,

message TEXT NOT NULL,

status ENUM(
    'open',
    'closed'
) DEFAULT 'open',

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- APPOINTMENTS
-- ==========================================================

CREATE TABLE appointments (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

tenant_id BIGINT UNSIGNED NOT NULL,

agent_id BIGINT UNSIGNED NULL,

appointment_date DATETIME NOT NULL,

status ENUM(
    'pending',
    'approved',
    'cancelled',
    'completed'
) DEFAULT 'pending',

notes TEXT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- REVIEWS
-- ==========================================================

CREATE TABLE reviews (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
property_id BIGINT UNSIGNED NOT NULL,

user_id BIGINT UNSIGNED NOT NULL,

rating INT NOT NULL,

review TEXT,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(property_id)
REFERENCES properties(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- NOTIFICATIONS
-- ==========================================================

CREATE TABLE notifications (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

title VARCHAR(255),

message TEXT,

is_read TINYINT(1) DEFAULT 0,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- TENANT SCREENING
-- ==========================================================

CREATE TABLE tenant_screenings (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
tenant_id BIGINT UNSIGNED NOT NULL,

employment_status VARCHAR(255),

monthly_income DECIMAL(15,2),

recommendation ENUM(
    'approved',
    'review',
    'rejected'
) DEFAULT 'review',

score INT DEFAULT 0,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

);

-- ==========================================================
-- GOOGLE OAUTH
-- ==========================================================

CREATE TABLE oauth_accounts (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

provider VARCHAR(50) NOT NULL,

provider_user_id VARCHAR(255) NOT NULL,

access_token TEXT NULL,

refresh_token TEXT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- MPESA
-- ==========================================================

CREATE TABLE mpesa_transactions (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NOT NULL,

amount DECIMAL(15,2) NOT NULL,

phone VARCHAR(20) NOT NULL,

merchant_request_id VARCHAR(255),

checkout_request_id VARCHAR(255),

mpesa_receipt VARCHAR(255),

status ENUM(
    'pending',
    'completed',
    'failed'
) DEFAULT 'pending',

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

FOREIGN KEY(user_id)
REFERENCES users(id)
ON DELETE CASCADE
```

);

-- ==========================================================
-- PASSWORD RESETS
-- ==========================================================

CREATE TABLE password_resets (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
email VARCHAR(191) NOT NULL,

token VARCHAR(255) NOT NULL,

expires_at DATETIME NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

);

-- ==========================================================
-- ANALYTICS
-- ==========================================================

CREATE TABLE analytics_events (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

```
user_id BIGINT UNSIGNED NULL,

property_id BIGINT UNSIGNED NULL,

event_type VARCHAR(100),

event_data JSON NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

);
