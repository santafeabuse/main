-- Add chat_instructions table for custom user instructions
CREATE TABLE IF NOT EXISTS chat_instructions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add custom_instructions_id column to chat_sessions table
ALTER TABLE chat_sessions 
ADD COLUMN custom_instructions_id INT DEFAULT NULL,
ADD FOREIGN KEY (custom_instructions_id) REFERENCES chat_instructions(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX idx_chat_instructions_user_id ON chat_instructions(user_id);
CREATE INDEX idx_chat_sessions_custom_instructions_id ON chat_sessions(custom_instructions_id);