ALTER TABLE users
	ADD COLUMN comment_email_notifications TINYINT(1) NOT NULL DEFAULT 1 AFTER email_verified_at;
