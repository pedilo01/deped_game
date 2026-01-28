-- Create database
CREATE DATABASE IF NOT EXISTS reading_adventure;
USE reading_adventure;

-- Users table (if you want multiple users)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Game sessions table
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT 1, -- Default to guest user
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    total_score INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    duration_seconds INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Questions answered table
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    game_type ENUM('phonics', 'sightwords', 'vocabulary', 'spelling', 'comprehension', 'sentences') NOT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    word VARCHAR(100),
    is_correct BOOLEAN DEFAULT FALSE,
    time_taken DECIMAL(5,2) DEFAULT 0, -- Time in seconds
    score_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Words learned table
CREATE TABLE words_learned (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT 1,
    word VARCHAR(100) NOT NULL,
    game_type VARCHAR(50),
    difficulty VARCHAR(50),
    times_correct INT DEFAULT 0,
    times_attempted INT DEFAULT 0,
    first_learned TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_practiced TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_word_user (user_id, word)
);

-- Progress tracking table
CREATE TABLE progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT 1,
    date DATE NOT NULL,
    total_score INT DEFAULT 0,
    questions_answered INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    accuracy DECIMAL(5,2) DEFAULT 0,
    games_played INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date)
);

-- Achievements table
CREATE TABLE achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT 1,
    achievement_type VARCHAR(50) NOT NULL,
    achievement_name VARCHAR(100) NOT NULL,
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert a default guest user
INSERT INTO users (username, email) VALUES ('guest', 'guest@example.com');

-- Create indexes for better performance
CREATE INDEX idx_questions_session ON questions(session_id);
CREATE INDEX idx_questions_date ON questions(created_at);
CREATE INDEX idx_sessions_user ON sessions(user_id);
CREATE INDEX idx_sessions_date ON sessions(start_time);