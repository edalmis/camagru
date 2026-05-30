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
