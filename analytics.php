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
        /* CSS moved to css/analytics.css */
    </style>
    <link rel="stylesheet" href="css/analytics.css">
</head>

<body>
    <div class="container"
        style="max-width: 1200px; margin: 0 auto; background: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <?php include 'navbar.php'; ?>

        <div class="header">
            <h1>📊 Reading & Math Adventure Analytics</h1>
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
            function getGameIcon($game)
            {
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



        <?php $conn->close(); ?>
</body>

</html>