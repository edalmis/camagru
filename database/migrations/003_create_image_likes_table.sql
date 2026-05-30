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
