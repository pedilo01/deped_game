<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Math Adventure - Interactive Math Games</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@700&family=Fredoka+One&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/math_adventure.css">
</head>

<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-calculator"></i> Math Adventure <i class="fas fa-star"></i></h1>
            <p class="tagline">Learn Math Through Fun Games!</p>
            <div id="dbStatus" class="db-status db-disconnected">
                <i class="fas fa-database"></i> Testing database connection...
            </div>
        </header>

        <div class="game-area">
            <!-- Left Panel - Controls -->
            <div class="left-panel">
                <h2 class="panel-title"><i class="fas fa-gamepad"></i> Math Games</h2>
                <div class="game-buttons" id="gameButtons">
                    <button class="game-btn active" data-game="addition">
                        <i class="fas fa-plus"></i><br>Addition
                    </button>
                    <button class="game-btn" data-game="subtraction">
                        <i class="fas fa-minus"></i><br>Subtraction
                    </button>
                    <button class="game-btn" data-game="multiplication">
                        <i class="fas fa-times"></i><br>Multiplication
                    </button>
                    <button class="game-btn" data-game="division">
                        <i class="fas fa-divide"></i><br>Division
                    </button>
                    <button class="game-btn" data-game="mixed">
                        <i class="fas fa-random"></i><br>Mixed
                    </button>
                    <button class="game-btn" data-game="counting">
                        <i class="fas fa-sort-numeric-up"></i><br>Numbers
                    </button>
                </div>

                <h2 class="panel-title"><i class="fas fa-trophy"></i> Difficulty Level</h2>
                <div class="difficulty-buttons" id="difficultyButtons">
                    <button class="diff-btn difficulty-easy active" data-difficulty="easy">
                        <i class="fas fa-seedling"></i> Easy
                    </button>
                    <button class="diff-btn difficulty-medium" data-difficulty="medium">
                        <i class="fas fa-apple-alt"></i> Medium
                    </button>
                    <button class="diff-btn difficulty-hard" data-difficulty="hard">
                        <i class="fas fa-fire"></i> Hard
                    </button>
                    <button class="diff-btn difficulty-expert" data-difficulty="expert">
                        <i class="fas fa-crown"></i> Expert
                    </button>
                </div>

                <div class="timer-container">
                    <div class="timer-text">Time Remaining</div>
                    <div class="timer-value" id="timer">60</div>
                </div>

                <div class="stats">
                    <h2 class="panel-title"><i class="fas fa-chart-line"></i> Game Stats</h2>
                    <div class="stat-row">
                        <span>Score:</span>
                        <span id="scoreValue">0</span>
                    </div>
                    <div class="stat-row">
                        <span>Streak:</span>
                        <span id="streakValue">0</span>
                    </div>
                    <div class="stat-row">
                        <span>Correct:</span>
                        <span id="correctValue">0/0</span>
                    </div>
                    <div class="stat-row">
                        <span>Saved:</span>
                        <span id="savedValue">0</span>
                    </div>
                </div>

                <div class="nav-buttons">
                    <a href="index.php" class="nav-btn reading">
                        <i class="fas fa-book"></i> Reading Adventure
                    </a>
                    <a href="analytics.php" class="nav-btn analytics">
                        <i class="fas fa-chart-bar"></i> View Analytics
                    </a>
                </div>
            </div>

            <!-- Right Panel - Game -->
            <div class="right-panel">
                <div class="game-display" id="gameDisplay">
                    <h2 id="gameQuestion">Solve the math problem:</h2>
                    <div class="math-problem" id="mathProblem">5 + 3 = ?</div>

                    <div class="answer-input-container">
                        <input type="number" class="answer-input" id="answerInput" placeholder="Enter answer" autofocus>
                    </div>

                    <div class="feedback" id="feedback">
                        Enter your answer and press Submit!
                    </div>

                    <div class="controls">
                        <button class="control-btn submit-btn" id="submitBtn">
                            <i class="fas fa-check"></i> Submit Answer
                        </button>
                        <button class="control-btn next-btn" id="nextBtn">
                            <i class="fas fa-forward"></i> Next
                        </button>
                        <button class="control-btn reset-btn" id="resetBtn">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="additional-stats">
                    <div class="stat-card">
                        <div class="stat-card-value" id="totalQuestions">0</div>
                        <div class="stat-card-label">Questions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value" id="accuracy">0%</div>
                        <div class="stat-card-label">Accuracy</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value" id="avgTime">0s</div>
                        <div class="stat-card-label">Avg. Time</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value" id="sessionId">-</div>
                        <div class="stat-card-label">Session</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================= CONFIGURATION =================
        const API_URL = 'api.php'; // Same directory

        // ================= GAME STATE =================
        const gameState = {
            currentGame: 'addition',
            difficulty: 'easy',
            score: 0,
            streak: 0,
            totalQuestions: 0,
            correctAnswers: 0,
            currentProblem: {},
            timer: 60,
            timerInterval: null,
            isGameActive: false,
            startTime: null,
            totalTime: 0,
            // Database properties
            sessionId: null,
            dbConnected: false,
            savedCount: 0
        };

        // DOM elements
        const gameBtns = document.querySelectorAll('.game-btn');
        const difficultyBtns = document.querySelectorAll('.diff-btn');
        const mathProblemEl = document.getElementById('mathProblem');
        const answerInputEl = document.getElementById('answerInput');
        const submitBtn = document.getElementById('submitBtn');
        const nextBtn = document.getElementById('nextBtn');
        const resetBtn = document.getElementById('resetBtn');
        const feedbackEl = document.getElementById('feedback');
        const scoreEl = document.getElementById('scoreValue');
        const streakEl = document.getElementById('streakValue');
        const correctEl = document.getElementById('correctValue');
        const savedEl = document.getElementById('savedValue');
        const timerEl = document.getElementById('timer');
        const totalQuestionsEl = document.getElementById('totalQuestions');
        const accuracyEl = document.getElementById('accuracy');
        const avgTimeEl = document.getElementById('avgTime');
        const sessionIdEl = document.getElementById('sessionId');
        const dbStatusEl = document.getElementById('dbStatus');

        // Difficulty settings
        const difficultySettings = {
            easy: { min: 1, max: 10, time: 60, points: 10 },
            medium: { min: 5, max: 30, time: 45, points: 15 },
            hard: { min: 20, max: 100, time: 30, points: 20 },
            expert: { min: 50, max: 200, time: 20, points: 25 }
        };

        // ================= DATABASE FUNCTIONS =================
        async function testDBConnection() {
            try {
                console.log('Testing database connection...');
                const response = await fetch(`${API_URL}?endpoint=test_connection`);
                const data = await response.json();

                if (data.success) {
                    gameState.dbConnected = true;
                    dbStatusEl.className = 'db-status db-connected';
                    dbStatusEl.innerHTML = '<i class="fas fa-database"></i> Database Connected';
                    console.log('Database connection successful');
                    await startSession();
                } else {
                    throw new Error('Connection test failed');
                }
            } catch (error) {
                console.log('Database offline:', error.message);
                gameState.dbConnected = false;
                dbStatusEl.className = 'db-status db-disconnected';
                dbStatusEl.innerHTML = '<i class="fas fa-database"></i> Database Offline (Using Local Storage)';
            }
        }

        async function startSession() {
            if (!gameState.dbConnected) return;

            try {
                const response = await fetch(`${API_URL}?endpoint=start_session`, {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    gameState.sessionId = data.session_id;
                    sessionIdEl.textContent = gameState.sessionId;
                    console.log('Session started:', gameState.sessionId);
                }
            } catch (error) {
                console.log('Failed to start session:', error);
            }
        }

        async function saveQuestion(isCorrect, timeTaken, word) {
            if (!gameState.dbConnected || !gameState.sessionId) {
                console.log('Cannot save: Database not connected or no session');
                return;
            }

            try {
                const data = {
                    session_id: gameState.sessionId,
                    game_type: gameState.currentGame,
                    difficulty: gameState.difficulty,
                    word: word || '',
                    is_correct: isCorrect,
                    time_taken: timeTaken,
                    score_earned: isCorrect ? difficultySettings[gameState.difficulty].points : 0
                };

                console.log('Saving question:', data);

                const response = await fetch(`${API_URL}?endpoint=save_question`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    gameState.savedCount++;
                    savedEl.textContent = gameState.savedCount;
                    console.log('Question saved to database:', result);
                } else {
                    console.log('Failed to save question:', result);
                }
            } catch (error) {
                console.log('Failed to save question:', error);
            }
        }

        // ================= GAME FUNCTIONS =================
        async function initGame() {
            console.log('Initializing Math Adventure...');

            // Test database connection
            await testDBConnection();

            // Set up event listeners
            gameBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    gameBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    gameState.currentGame = btn.dataset.game;
                    generateProblem();
                    updateUI();
                });
            });

            difficultyBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    difficultyBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    gameState.difficulty = btn.dataset.difficulty;
                    gameState.timer = difficultySettings[gameState.difficulty].time;
                    timerEl.textContent = gameState.timer;
                    generateProblem();
                    updateUI();
                });
            });

            submitBtn.addEventListener('click', checkAnswer);
            nextBtn.addEventListener('click', generateProblem);
            resetBtn.addEventListener('click', resetGame);

            // Allow pressing Enter to submit answer
            answerInputEl.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    checkAnswer();
                }
            });

            // Ensure input is always focusable
            answerInputEl.addEventListener('blur', () => {
                setTimeout(() => answerInputEl.focus(), 100);
            });

            // Start the game
            generateProblem();
            startTimer();
            updateUI();

            // Focus the input
            answerInputEl.focus();
        }

        // Generate a math problem
        function generateProblem() {
            const settings = difficultySettings[gameState.difficulty];
            const { min, max } = settings;

            // Clear feedback
            feedbackEl.textContent = 'Enter your answer!';
            feedbackEl.className = 'feedback';

            // Focus on input and clear it
            answerInputEl.focus();
            answerInputEl.value = '';
            answerInputEl.disabled = false;

            // Record start time for this question
            gameState.startTime = Date.now();

            let problem = {};

            switch (gameState.currentGame) {
                case 'addition':
                    const a = getRandomInt(min, max);
                    const b = getRandomInt(min, max);
                    problem = {
                        question: `${a} + ${b} = ?`,
                        answer: a + b
                    };
                    break;

                case 'subtraction':
                    let x = getRandomInt(min, max);
                    let y = getRandomInt(min, max);
                    if (x < y) [x, y] = [y, x];
                    problem = {
                        question: `${x} - ${y} = ?`,
                        answer: x - y
                    };
                    break;

                case 'multiplication':
                    const m = getRandomInt(min, Math.floor(max / 5));
                    const n = getRandomInt(min, Math.floor(max / 5));
                    problem = {
                        question: `${m} × ${n} = ?`,
                        answer: m * n
                    };
                    break;

                case 'division':
                    const divisor = getRandomInt(2, 10);
                    const quotient = getRandomInt(min, Math.floor(max / 5));
                    const dividend = divisor * quotient;
                    problem = {
                        question: `${dividend} ÷ ${divisor} = ?`,
                        answer: quotient
                    };
                    break;

                case 'mixed':
                    const operations = ['+', '-', '×', '÷'];
                    const op = operations[getRandomInt(0, operations.length - 1)];

                    let num1, num2, result;

                    if (op === '+') {
                        num1 = getRandomInt(min, max);
                        num2 = getRandomInt(min, max);
                        result = num1 + num2;
                    } else if (op === '-') {
                        num1 = getRandomInt(min, max);
                        num2 = getRandomInt(min, max);
                        if (num1 < num2) [num1, num2] = [num2, num1];
                        result = num1 - num2;
                    } else if (op === '×') {
                        num1 = getRandomInt(min, Math.floor(max / 5));
                        num2 = getRandomInt(min, Math.floor(max / 5));
                        result = num1 * num2;
                    } else {
                        const divisor2 = getRandomInt(2, 10);
                        const quotient2 = getRandomInt(min, Math.floor(max / 5));
                        num1 = divisor2 * quotient2;
                        num2 = divisor2;
                        result = quotient2;
                    }

                    problem = {
                        question: `${num1} ${op} ${num2} = ?`,
                        answer: result
                    };
                    break;

                case 'counting':
                    const problemType = getRandomInt(1, 3);
                    if (problemType === 1) {
                        const start = getRandomInt(min, max);
                        problem = {
                            question: `${start}, ${start + 1}, ${start + 2}, ?`,
                            answer: start + 3
                        };
                    } else if (problemType === 2) {
                        const numA = getRandomInt(min, max);
                        const numB = getRandomInt(min, max);
                        problem = {
                            question: `Which is bigger: ${numA} or ${numB}?`,
                            answer: Math.max(numA, numB)
                        };
                    } else {
                        const countBy = [2, 5, 10][getRandomInt(0, 2)];
                        const startCount = getRandomInt(1, 10) * countBy;
                        problem = {
                            question: `Count by ${countBy}s: ${startCount}, ${startCount + countBy}, ?`,
                            answer: startCount + (countBy * 2)
                        };
                    }
                    break;

                default:
                    problem = {
                        question: `5 + 3 = ?`,
                        answer: 8
                    };
            }

            gameState.currentProblem = problem;
            mathProblemEl.textContent = problem.question;
        }

        // Check if the user's answer is correct
        async function checkAnswer() {
            const userAnswer = parseFloat(answerInputEl.value);
            const correctAnswer = gameState.currentProblem.answer;

            if (isNaN(userAnswer)) {
                feedbackEl.textContent = 'Please enter a number!';
                feedbackEl.className = 'feedback incorrect';
                return;
            }

            // Calculate time taken for this question
            let timeTaken = 0;
            if (gameState.startTime) {
                timeTaken = (Date.now() - gameState.startTime) / 1000;
                gameState.totalTime += timeTaken;
            }

            gameState.totalQuestions++;

            const isCorrect = Math.abs(userAnswer - correctAnswer) < 0.001;

            // Save to database
            const word = `${gameState.currentProblem.question.replace('?', '')} ${correctAnswer}`;
            await saveQuestion(isCorrect, timeTaken, word);

            if (isCorrect) {
                const points = difficultySettings[gameState.difficulty].points;
                gameState.score += points;
                gameState.streak++;
                gameState.correctAnswers++;

                feedbackEl.textContent = `Correct! +${points} points!`;
                feedbackEl.className = 'feedback correct';
            } else {
                gameState.streak = 0;
                feedbackEl.textContent = `Oops! The answer is ${correctAnswer}.`;
                feedbackEl.className = 'feedback incorrect';
            }

            updateUI();

            // Disable input after answering
            answerInputEl.disabled = true;

            // Auto-generate next problem after a delay
            setTimeout(() => {
                if (gameState.isGameActive) {
                    generateProblem();
                }
            }, 2000);
        }

        // Update the UI with current game state
        function updateUI() {
            scoreEl.textContent = gameState.score;
            streakEl.textContent = gameState.streak;
            correctEl.textContent = `${gameState.correctAnswers}/${gameState.totalQuestions}`;
            savedEl.textContent = gameState.savedCount;
            totalQuestionsEl.textContent = gameState.totalQuestions;

            const accuracy = gameState.totalQuestions > 0
                ? Math.round((gameState.correctAnswers / gameState.totalQuestions) * 100)
                : 0;
            accuracyEl.textContent = `${accuracy}%`;

            // Calculate average time per question
            const avgTime = gameState.totalQuestions > 0
                ? (gameState.totalTime / gameState.totalQuestions).toFixed(1)
                : 0;
            avgTimeEl.textContent = `${avgTime}s`;

            sessionIdEl.textContent = gameState.sessionId || '-';
        }

        // Start the game timer
        function startTimer() {
            if (gameState.timerInterval) {
                clearInterval(gameState.timerInterval);
            }

            gameState.isGameActive = true;
            gameState.timer = difficultySettings[gameState.difficulty].time;
            timerEl.textContent = gameState.timer;

            gameState.timerInterval = setInterval(() => {
                gameState.timer--;
                timerEl.textContent = gameState.timer;

                if (gameState.timer <= 10) {
                    timerEl.style.color = '#ff0000';
                } else {
                    timerEl.style.color = '#ff5e62';
                }

                if (gameState.timer <= 0) {
                    endGame();
                }
            }, 1000);
        }

        // End the game when time runs out
        function endGame() {
            gameState.isGameActive = false;
            clearInterval(gameState.timerInterval);

            feedbackEl.textContent = `Time's up! Final score: ${gameState.score}`;
            feedbackEl.className = 'feedback incorrect';

            // Disable input and submit button
            answerInputEl.disabled = true;
            submitBtn.disabled = true;
        }

        // Reset the game to initial state
        function resetGame() {
            gameState.score = 0;
            gameState.streak = 0;
            gameState.totalQuestions = 0;
            gameState.correctAnswers = 0;
            gameState.totalTime = 0;

            // Reset timer color
            timerEl.style.color = '#ff5e62';

            // Re-enable input and submit button
            answerInputEl.disabled = false;
            submitBtn.disabled = false;

            // Reset timer
            startTimer();

            // Generate new problem
            generateProblem();

            // Update UI
            updateUI();

            feedbackEl.textContent = 'New game started! Good luck!';
            feedbackEl.className = 'feedback';

            // Focus input
            answerInputEl.focus();
        }

        // Utility function to get a random integer
        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        // Initialize the game when page loads
        window.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>

</html>