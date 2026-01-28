<?php
require_once 'config.php';

// Get the endpoint from URL
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response(['success' => true]);
}

// Route the request
switch ($endpoint) {
    case 'test_connection':
        testConnection();
        break;
    case 'start_session':
        startSession();
        break;
    case 'save_question':
        saveQuestion();
        break;
    case 'get_stats':
        getStats();
        break;
    default:
        json_response([
            'api' => 'Reading Adventure API',
            'version' => '1.0',
            'endpoints' => [
                '/api.php?endpoint=test_connection' => 'Test database connection',
                '/api.php?endpoint=start_session' => 'Start new session',
                '/api.php?endpoint=save_question' => 'Save question data',
                '/api.php?endpoint=get_stats' => 'Get game statistics'
            ]
        ]);
}

// Test database connection
function testConnection() {
    $conn = getDBConnection();
    
    if ($conn) {
        json_response([
            'success' => true,
            'message' => 'Database connected successfully',
            'database' => 'reading_adventure',
            'tables' => ['users', 'sessions', 'questions', 'words_learned', 'progress']
        ]);
    } else {
        json_response([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => 'Make sure MySQL is running and database exists'
        ], 500);
    }
}

// Start a new session
function startSession() {
    $conn = getDBConnection();
    
    if (!$conn) {
        json_response(['success' => false, 'message' => 'Database not connected'], 500);
    }
    
    // Start new session
    $sql = "INSERT INTO sessions (user_id, start_time) VALUES (1, NOW())";
    
    if ($conn->query($sql) === TRUE) {
        $session_id = $conn->insert_id;
        json_response([
            'success' => true,
            'session_id' => $session_id,
            'message' => 'Session started'
        ]);
    } else {
        json_response(['success' => false, 'message' => 'Failed to start session'], 500);
    }
    
    $conn->close();
}

// Save question data
function saveQuestion() {
    $conn = getDBConnection();
    
    if (!$conn) {
        json_response(['success' => false, 'message' => 'Database not connected'], 500);
    }
    
    // Get JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        json_response(['success' => false, 'message' => 'Invalid data'], 400);
    }
    
    // Extract data
    $session_id = isset($input['session_id']) ? intval($input['session_id']) : 0;
    $game_type = isset($input['game_type']) ? $conn->real_escape_string($input['game_type']) : '';
    $difficulty = isset($input['difficulty']) ? $conn->real_escape_string($input['difficulty']) : '';
    $word = isset($input['word']) ? $conn->real_escape_string($input['word']) : '';
    $is_correct = isset($input['is_correct']) ? ($input['is_correct'] ? 1 : 0) : 0;
    $time_taken = isset($input['time_taken']) ? floatval($input['time_taken']) : 0;
    $score_earned = isset($input['score_earned']) ? intval($input['score_earned']) : ($is_correct ? 10 : 0);
    
    // Map game types for database compatibility
    if ($game_type === 'sentencebuilder') {
        $game_type = 'sentences';
    }
    
    if ($session_id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid session ID'], 400);
    }
    
    // Save question
    $sql = "INSERT INTO questions (session_id, game_type, difficulty, word, is_correct, time_taken, score_earned) 
            VALUES ($session_id, '$game_type', '$difficulty', '$word', $is_correct, $time_taken, $score_earned)";
    
    if ($conn->query($sql) === TRUE) {
        // Update word stats if word exists
        if (!empty($word)) {
            updateWordStats($conn, $word, $game_type, $difficulty, $is_correct);
        }
        
        json_response([
            'success' => true,
            'message' => 'Question saved',
            'question_id' => $conn->insert_id
        ]);
    } else {
        json_response(['success' => false, 'message' => 'Failed to save question: ' . $conn->error], 500);
    }
    
    $conn->close();
}
// Update word statistics
function updateWordStats($conn, $word, $game_type, $difficulty, $is_correct) {
    // Check if word exists
    $sql = "SELECT id FROM words_learned WHERE user_id = 1 AND word = '$word'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Update existing word
        $sql = "UPDATE words_learned SET 
                times_attempted = times_attempted + 1,
                times_correct = times_correct + " . ($is_correct ? 1 : 0) . ",
                last_practiced = NOW()
                WHERE user_id = 1 AND word = '$word'";
    } else {
        // Insert new word
        $sql = "INSERT INTO words_learned (user_id, word, game_type, difficulty, times_attempted, times_correct, last_practiced)
                VALUES (1, '$word', '$game_type', '$difficulty', 1, " . ($is_correct ? 1 : 0) . ", NOW())";
    }
    
    $conn->query($sql);
}

// Get statistics
function getStats() {
    $conn = getDBConnection();
    
    if (!$conn) {
        json_response(['success' => false, 'message' => 'Database not connected'], 500);
    }
    
    $stats = [];
    
    // Total sessions
    $result = $conn->query("SELECT COUNT(*) as count FROM sessions");
    $row = $result->fetch_assoc();
    $stats['total_sessions'] = intval($row['count']);
    
    // Total questions
    $result = $conn->query("SELECT COUNT(*) as count FROM questions");
    $row = $result->fetch_assoc();
    $stats['total_questions'] = intval($row['count']);
    
    // Accuracy
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(is_correct) as correct,
        ROUND(SUM(is_correct) / COUNT(*) * 100, 1) as accuracy
        FROM questions");
    $row = $result->fetch_assoc();
    $stats['accuracy'] = $row;
    
    // Recent questions
    $result = $conn->query("SELECT * FROM questions ORDER BY created_at DESC LIMIT 10");
    $stats['recent_questions'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['recent_questions'][] = $row;
    }
    
    $conn->close();
    
    json_response([
        'success' => true,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
}
?>