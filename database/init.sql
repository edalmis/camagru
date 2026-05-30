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

CREATE TABLE IF NOT EXISTS images (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id INT UNSIGNED NOT NULL,
	file_path VARCHAR(255) NOT NULL,
	original_name VARCHAR(255) NOT NULL,
	mime_type VARCHAR(100) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY idx_images_user_id (user_id),
	CONSTRAINT fk_images_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS image_likes (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	image_id INT UNSIGNED NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY unique_image_like (image_id, user_id),
	KEY idx_image_likes_image_id (image_id),
	KEY idx_image_likes_user_id (user_id),
	CONSTRAINT fk_image_likes_image_id FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
	CONSTRAINT fk_image_likes_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS image_comments (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	image_id INT UNSIGNED NOT NULL,
	user_id INT UNSIGNED NOT NULL,
	comment_text TEXT NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY idx_image_comments_image_id (image_id),
	KEY idx_image_comments_user_id (user_id),
	CONSTRAINT fk_image_comments_image_id FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
	CONSTRAINT fk_image_comments_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS auth_tokens (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id INT UNSIGNED NOT NULL,
	token_hash CHAR(64) NOT NULL,
	expires_at TIMESTAMP NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY unique_token_hash (token_hash),
	KEY idx_auth_tokens_user_id (user_id),
	CONSTRAINT fk_auth_tokens_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Database is ready for table creation
