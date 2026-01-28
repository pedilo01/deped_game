<?php
require_once 'config.php';

$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed. Please run setup.php first.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reading & Math Adventure Analytics</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { color: #333; }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            text-align: center;
            border-top: 5px solid;
        }
        .stat-card.reading { border-color: #5d8aa8; }
        .stat-card.math { border-color: #ff5e62; }
        .stat-card.overall { border-color: #36d1dc; }
        .stat-value { 
            font-size: 2.5em; 
            font-weight: bold; 
            margin: 10px 0;
            font-family: 'Fredoka One', cursive;
        }
        .reading .stat-value { color: #5d8aa8; }
        .math .stat-value { color: #ff5e62; }
        .overall .stat-value { color: #36d1dc; }
        .stat-label { 
            color: #666; 
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        .stat-subtext { 
            color: #888; 
            font-size: 0.9em;
            margin-top: 5px;
        }
        .section-title { 
            color: #333; 
            margin: 30px 0 15px 0; 
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .game-stats { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .game-card { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .game-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 15px;
        }
        .game-name { 
            font-weight: bold; 
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .game-icon { font-size: 1.5em; }
        .reading-game { color: #5d8aa8; }
        .math-game { color: #ff5e62; }
        .game-metrics { 
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }
        .metric-value { font-size: 1.3em; font-weight: bold; }
        .metric-label { font-size: 0.9em; color: #666; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
        }
        th { 
            background: #5d8aa8; 
            color: white; 
            font-weight: bold;
        }
        tr:hover { background: #f9f9f9; }
        .correct { color: #2e7d32; font-weight: bold; }
        .incorrect { color: #d32f2f; }
        .nav-buttons { display: flex; gap: 10px; margin-top: 30px; }
        .nav-btn { 
            padding: 12px 25px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .back-btn { 
            background: #5d8aa8; 
            color: white;
        }
        .math-btn { 
            background: #ff5e62; 
            color: white;
        }
        .nav-btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reading & Math Adventure Analytics</h1>
        <div class="nav-buttons">
            <a href="math_adventure.html" class="nav-btn math-btn">
                <i class="fas fa-calculator"></i> Play Math Games
            </a>
            <a href="index.php" class="nav-btn back-btn">
                <i class="fas fa-gamepad"></i> Back to Reading Games
            </a>
        </div>
    </div>
    
    <div class="stats-grid">
        <?php
        // Get overall statistics
        $result = $conn->query("SELECT COUNT(*) as total FROM sessions");
        $sessions = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM questions");
        $questions = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(is_correct) as correct,
            ROUND(SUM(is_correct) / COUNT(*) * 100, 1) as accuracy
            FROM questions");
        $accuracy = $result->fetch_assoc();
        
        $result = $conn->query("SELECT COUNT(DISTINCT word) as total FROM words_learned");
        $words = $result->fetch_assoc()['total'];
        
        // Get reading game stats
        $reading_games = ['phonics', 'sightwords', 'vocabulary', 'spelling', 'comprehension', 'sentences'];
        $reading_list = "'" . implode("','", $reading_games) . "'";
        
        $result = $conn->query("SELECT COUNT(*) as total FROM questions WHERE game_type IN ($reading_list)");
        $reading_questions = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(is_correct) as correct
            FROM questions WHERE game_type IN ($reading_list)");
        $reading_accuracy = $result->fetch_assoc();
        $reading_accuracy_rate = $reading_accuracy['total'] > 0 ? 
            round(($reading_accuracy['correct'] / $reading_accuracy['total']) * 100, 1) : 0;
        
        // Get math game stats
        $math_games = ['addition', 'subtraction', 'multiplication', 'division', 'mixed', 'counting'];
        $math_list = "'" . implode("','", $math_games) . "'";
        
        $result = $conn->query("SELECT COUNT(*) as total FROM questions WHERE game_type IN ($math_list)");
        $math_questions = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(is_correct) as correct
            FROM questions WHERE game_type IN ($math_list)");
        $math_accuracy = $result->fetch_assoc();
        $math_accuracy_rate = $math_accuracy['total'] > 0 ? 
            round(($math_accuracy['correct'] / $math_accuracy['total']) * 100, 1) : 0;
        ?>
        
        <div class="stat-card overall">
            <div class="stat-label">Total Sessions</div>
            <div class="stat-value"><?php echo $sessions; ?></div>
            <div class="stat-subtext">Games Played</div>
        </div>
        
        <div class="stat-card overall">
            <div class="stat-label">Questions Answered</div>
            <div class="stat-value"><?php echo $questions; ?></div>
            <div class="stat-subtext">Total Questions</div>
        </div>
        
        <div class="stat-card overall">
            <div class="stat-label">Overall Accuracy</div>
            <div class="stat-value"><?php echo $accuracy['accuracy'] ?? 0; ?>%</div>
            <div class="stat-subtext">Correct Answers</div>
        </div>
        
        <div class="stat-card overall">
            <div class="stat-label">Words & Problems</div>
            <div class="stat-value"><?php echo $words; ?></div>
            <div class="stat-subtext">Unique Items</div>
        </div>
        
        <div class="stat-card reading">
            <div class="stat-label">Reading Games</div>
            <div class="stat-value"><?php echo $reading_questions; ?></div>
            <div class="stat-subtext"><?php echo $reading_accuracy_rate; ?>% Accuracy</div>
        </div>
        
        <div class="stat-card math">
            <div class="stat-label">Math Games</div>
            <div class="stat-value"><?php echo $math_questions; ?></div>
            <div class="stat-subtext"><?php echo $math_accuracy_rate; ?>% Accuracy</div>
        </div>
    </div>
    
    <h2 class="section-title">📈 Game Performance</h2>
    <div class="game-stats">
        <?php
        // Get stats for each game type
        $games = array_merge($reading_games, $math_games);
        
        foreach ($games as $game):
            $game_name = $game;
            $game_icon = getGameIcon($game);
            $game_class = in_array($game, $math_games) ? 'math-game' : 'reading-game';
            
            $result = $conn->query("SELECT 
                COUNT(*) as total,
                SUM(is_correct) as correct,
                ROUND(SUM(is_correct) / COUNT(*) * 100, 1) as accuracy,
                AVG(time_taken) as avg_time,
                SUM(score_earned) as total_score
                FROM questions WHERE game_type = '$game'");
            $stats = $result->fetch_assoc();
            
            if ($stats['total'] > 0):
        ?>
        <div class="game-card">
            <div class="game-header">
                <div class="game-name <?php echo $game_class; ?>">
                    <i class="fas fa-<?php echo $game_icon; ?> game-icon"></i>
                    <?php echo ucfirst($game_name); ?>
                </div>
                <div class="game-accuracy"><?php echo $stats['accuracy']; ?>%</div>
            </div>
            <div class="game-metrics">
                <div>
                    <div class="metric-value"><?php echo $stats['total']; ?></div>
                    <div class="metric-label">Questions</div>
                </div>
                <div>
                    <div class="metric-value"><?php echo $stats['correct']; ?></div>
                    <div class="metric-label">Correct</div>
                </div>
                <div>
                    <div class="metric-value"><?php echo round($stats['avg_time'], 1); ?>s</div>
                    <div class="metric-label">Avg. Time</div>
                </div>
            </div>
        </div>
        <?php
            endif;
        endforeach;
        
        // Helper function for game icons
        function getGameIcon($game) {
            $icons = [
                'phonics' => 'spell-check',
                'sightwords' => 'eye',
                'vocabulary' => 'book-open',
                'spelling' => 'keyboard',
                'comprehension' => 'brain',
                'sentences' => 'align-left',
                'sentencebuilder' => 'align-left',
                'addition' => 'plus',
                'subtraction' => 'minus',
                'multiplication' => 'times',
                'division' => 'divide',
                'mixed' => 'random',
                'counting' => 'sort-numeric-up'
            ];
            return $icons[$game] ?? 'gamepad';
        }
        ?>
    </div>
    
    <h2 class="section-title">🕐 Recent Questions</h2>
    <table>
        <thead>
            <tr>
                <th>Game Type</th>
                <th>Problem/Word</th>
                <th>Difficulty</th>
                <th>Result</th>
                <th>Score</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM questions ORDER BY created_at DESC LIMIT 15");
            while ($row = $result->fetch_assoc()):
                $game_type = $row['game_type'];
                $is_math = in_array($game_type, $math_games);
                $game_display = ($game_type === 'sentences') ? 'sentencebuilder' : $game_type;
            ?>
            <tr>
                <td>
                    <span class="game-name <?php echo $is_math ? 'math-game' : 'reading-game'; ?>">
                        <i class="fas fa-<?php echo getGameIcon($game_type); ?>"></i>
                        <?php echo ucfirst($game_display); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($row['word'] ?: 'N/A'); ?></td>
                <td><?php echo ucfirst($row['difficulty']); ?></td>
                <td>
                    <?php if ($row['is_correct']): ?>
                        <span class="correct">✓ Correct</span>
                    <?php else: ?>
                        <span class="incorrect">✗ Incorrect</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $row['score_earned']; ?> pts</td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <h2 class="section-title">🏆 Top Performed Items</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Game Type</th>
                <th>Attempts</th>
                <th>Correct</th>
                <th>Accuracy</th>
                <th>Last Practiced</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT 
                word, 
                game_type,
                times_attempted, 
                times_correct, 
                ROUND(times_correct / times_attempted * 100, 1) as accuracy,
                DATE_FORMAT(last_practiced, '%Y-%m-%d %H:%i') as last_practiced
                FROM words_learned 
                WHERE times_attempted > 0 
                ORDER BY times_attempted DESC, accuracy DESC 
                LIMIT 15");
            while ($row = $result->fetch_assoc()):
                $is_math = in_array($row['game_type'], $math_games);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['word']); ?></td>
                <td>
                    <span class="game-name <?php echo $is_math ? 'math-game' : 'reading-game'; ?>">
                        <i class="fas fa-<?php echo getGameIcon($row['game_type']); ?>"></i>
                        <?php echo ucfirst($row['game_type'] === 'sentences' ? 'sentencebuilder' : $row['game_type']); ?>
                    </span>
                </td>
                <td><?php echo $row['times_attempted']; ?></td>
                <td><?php echo $row['times_correct']; ?></td>
                <td><?php echo $row['accuracy']; ?>%</td>
                <td><?php echo $row['last_practiced']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div class="nav-buttons">
        <a href="math_adventure.html" class="nav-btn math-btn">
            <i class="fas fa-calculator"></i> Play Math Games
        </a>
        <a href="index.php" class="nav-btn back-btn">
            <i class="fas fa-arrow-left"></i> Back to Reading Games
        </a>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>