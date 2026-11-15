ALTER TABLE users
    CHANGE COLUMN username legacy_username VARCHAR(191) NULL,
    ADD COLUMN phone_number VARCHAR(16) NULL,
    ADD COLUMN sms_reminders_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD UNIQUE KEY uq_users_phone_number (phone_number);

CREATE TABLE sms_reminder_log (
    cycle_id BIGINT UNSIGNED NOT NULL,
    reminder_type VARCHAR(32) NOT NULL,
    status ENUM('sending', 'sent') NOT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (cycle_id, reminder_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
