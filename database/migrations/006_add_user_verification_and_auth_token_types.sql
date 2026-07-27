ALTER TABLE users
	ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER password_hash;

ALTER TABLE auth_tokens
	ADD COLUMN token_type VARCHAR(32) NOT NULL DEFAULT 'password_reset' AFTER user_id,
	ADD COLUMN used_at TIMESTAMP NULL DEFAULT NULL AFTER expires_at,
	ADD INDEX idx_auth_tokens_type (token_type);
