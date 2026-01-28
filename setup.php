<?php
// Database Setup Tool for Reading Adventure
echo "<h1>Reading Adventure Database Setup</h1>";

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'reading_adventure';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL successfully.<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Create tables
$tables = [
    "users" => "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )",
    
    "sessions" => "CREATE TABLE sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT DEFAULT 1,
        start_time DATETIME NOT NULL,
        end_time DATETIME NULL,
        total_score INT DEFAULT 0,
        total_questions INT DEFAULT 0,
        correct_answers INT DEFAULT 0,
        duration_seconds INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    "questions" => "CREATE TABLE questions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT NOT NULL,
        game_type VARCHAR(50) NOT NULL,
        difficulty VARCHAR(50) NOT NULL,
        word VARCHAR(100),
        is_correct BOOLEAN DEFAULT FALSE,
        time_taken DECIMAL(5,2) DEFAULT 0,
        score_earned INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
    )",
    
    "words_learned" => "CREATE TABLE words_learned (
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
    )",
    
    "progress" => "CREATE TABLE progress (
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
    )",
    
    "achievements" => "CREATE TABLE achievements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT DEFAULT 1,
        achievement_type VARCHAR(50) NOT NULL,
        achievement_name VARCHAR(100) NOT NULL,
        achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $table_name => $sql) {
    // Drop table if exists (for fresh setup)
    $conn->query("DROP TABLE IF EXISTS $table_name");
    
    if ($conn->query($sql) === TRUE) {
        echo "Table '$table_name' created successfully.<br>";
    } else {
        echo "Error creating table '$table_name': " . $conn->error . "<br>";
    }
}

// Create indexes
$indexes = [
    "CREATE INDEX idx_questions_session ON questions(session_id)",
    "CREATE INDEX idx_questions_date ON questions(created_at)",
    "CREATE INDEX idx_sessions_user ON sessions(user_id)",
    "CREATE INDEX idx_sessions_date ON sessions(start_time)",
    "CREATE INDEX idx_words_user ON words_learned(user_id)",
    "CREATE INDEX idx_progress_user_date ON progress(user_id, date)"
];

foreach ($indexes as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Index created successfully.<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

// Insert default user
$sql = "INSERT IGNORE INTO users (id, username, email) VALUES (1, 'guest', 'guest@example.com')";
if ($conn->query($sql) === TRUE) {
    echo "Default user created.<br>";
} else {
    echo "Error creating user: " . $conn->error . "<br>";
}

echo "<h2>Database Setup Complete!</h2>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li><a href='index.php'>Play the Game</a></li>";
echo "<li><a href='api.php?endpoint=test_connection'>Test API Connection</a></li>";
echo "<li><a href='analytics.php'>View Analytics Dashboard</a></li>";
echo "</ul>";

$conn->close();
?>