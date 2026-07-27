CREATE TABLE IF NOT EXISTS auth_tokens (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id INT UNSIGNED NOT NULL,
	token_type VARCHAR(32) NOT NULL,
	token_hash CHAR(64) NOT NULL,
	expires_at TIMESTAMP NOT NULL,
	used_at TIMESTAMP NULL DEFAULT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY unique_token_hash (token_hash),
	KEY idx_auth_tokens_type (token_type),
	KEY idx_auth_tokens_user_id (user_id),
	CONSTRAINT fk_auth_tokens_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
