-- Camagru Database Initialization
-- This file initializes the database structure
-- Tables will be added in subsequent migrations

-- Set default collation and charset
ALTER DATABASE camagru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	username VARCHAR(30) NOT NULL,
	email VARCHAR(255) NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY unique_username (username),
	UNIQUE KEY unique_email (email)
);

-- Database is ready for table creation
