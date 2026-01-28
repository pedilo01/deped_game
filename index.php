<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Adventure - Interactive Reading Games</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-book"></i> Reading & Math Adventure <i class="fas fa-calculator"></i></h1>
            <p class="tagline">Learn Through Fun Games!</p>
            <div id="dbStatus" class="db-status db-disconnected">
                <i class="fas fa-database"></i> Testing database connection...
            </div>
        </header>
        
        <div class="game-area">
            <!-- Left Panel - Controls -->
            <div class="left-panel">
                <h2 class="panel-title"><i class="fas fa-gamepad"></i> Games</h2>
                <div class="game-buttons" id="gameButtons">
                    <button class="game-btn active" data-game="phonics">
                        <i class="fas fa-spell-check"></i><br>Phonics
                    </button>
                    <button class="game-btn" data-game="sightwords">
                        <i class="fas fa-eye"></i><br>Sight Words
                    </button>
                    <button class="game-btn" data-game="vocabulary">
                        <i class="fas fa-book-open"></i><br>Vocabulary
                    </button>
                    <button class="game-btn" data-game="spelling">
                        <i class="fas fa-keyboard"></i><br>Spelling
                    </button>
                    <button class="game-btn" data-game="comprehension">
                        <i class="fas fa-brain"></i><br>Comprehension
                    </button>
                    <button class="game-btn" data-game="sentencebuilder">
                        <i class="fas fa-align-left"></i><br>Sentence Builder
                    </button>
                    <!-- Math Button Added -->
                    <button class="game-btn math-btn" data-game="math">
                        <i class="fas fa-calculator"></i><br>Math Games
                    </button>
                    <button class="game-btn" data-game="mathadventure" onclick="window.location.href='math_adventure.php'">
                        <i class="fas fa-star"></i><br>Full Math Adventure
                    </button>
                </div>
                
                <h2 class="panel-title"><i class="fas fa-trophy"></i> Level</h2>
                <div class="difficulty-buttons" id="difficultyButtons">
                    <button class="diff-btn beginner active" data-difficulty="beginner">
                        <i class="fas fa-seedling"></i> Beginner
                    </button>
                    <button class="diff-btn intermediate" data-difficulty="intermediate">
                        <i class="fas fa-apple-alt"></i> Intermediate
                    </button>
                    <button class="diff-btn advanced" data-difficulty="advanced">
                        <i class="fas fa-fire"></i> Advanced
                    </button>
                    <button class="diff-btn expert" data-difficulty="expert">
                        <i class="fas fa-crown"></i> Expert
                    </button>
                </div>
                
                <div class="stats">
                    <h2 class="panel-title"><i class="fas fa-chart-line"></i> Stats</h2>
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
                        <span id="correctValue">0</span>
                    </div>
                    <div class="stat-row">
                        <span>Database:</span>
                        <span id="dbStats">0 saved</span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="./analytics.php" style="background:#36d1dc; color:white; border:none; padding:10px 20px; border-radius:10px; cursor:pointer; margin-right: 10px;">
                        <i class="fas fa-chart-bar"></i> View Analytics
                    </a>
                </div>
            </div>
            
            <!-- Right Panel - Game -->
            <div class="right-panel">
                <div class="game-display" id="gameDisplay">
                    <!-- Game content will be loaded here -->
                    <h2 id="gameQuestion">What sound does this letter make?</h2>
                    <div class="word-display" id="gameWord">B</div>
                    <div class="options" id="gameOptions">
                        <!-- Options will be loaded here -->
                    </div>
                    <div class="feedback" id="gameFeedback">
                        Select the correct answer!
                    </div>
                    <div class="controls">
                        <button class="control-btn submit-btn" id="submitBtn">
                            <i class="fas fa-check"></i> Check Answer
                        </button>
                        <button class="control-btn next-btn" id="nextBtn">
                            <i class="fas fa-forward"></i> Next
                        </button>
                        <button class="control-btn reset-btn" id="resetBtn">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================= CONFIGURATION =================
        const API_URL = 'api.php'; // Same directory
        
        // ================= GAME STATE =================
        let gameState = {
            currentGame: 'phonics',
            difficulty: 'beginner',
            score: 0,
            streak: 0,
            correct: 0,
            total: 0,
            sessionId: null,
            dbConnected: false,
            savedCount: 0,
            currentProblem: null,
            selectedOption: null,
            selectedWords: [], // For sentence builder
            currentSentence: [] // For sentence builder
        };
        
        // ================= SENTENCE BUILDER DATA =================
        const sentenceData = {
            beginner: [
                { words: ['I', 'see', 'the', 'cat', '.'], answer: 'I see the cat.', example: 'I see the cat.' },
                { words: ['My', 'mom', 'likes', 'to', 'read', '.'], answer: 'My mom likes to read.', example: 'My mom likes to read.' },
                { words: ['The', 'dog', 'runs', 'fast', '.'], answer: 'The dog runs fast.', example: 'The dog runs fast.' },
                { words: ['We', 'play', 'with', 'the', 'ball', '.'], answer: 'We play with the ball.', example: 'We play with the ball.' }
            ],
            intermediate: [
                { words: ['The', 'quick', 'brown', 'fox', 'jumped', '.'], answer: 'The quick brown fox jumped.', example: 'The quick brown fox jumped.' },
                { words: ['She', 'reads', 'books', 'every', 'day', '.'], answer: 'She reads books every day.', example: 'She reads books every day.' },
                { words: ['We', 'visited', 'the', 'beautiful', 'park', '.'], answer: 'We visited the beautiful park.', example: 'We visited the beautiful park.' },
                { words: ['My', 'favorite', 'color', 'is', 'blue', '.'], answer: 'My favorite color is blue.', example: 'My favorite color is blue.' }
            ],
            advanced: [
                { words: ['Reading', 'improves', 'your', 'vocabulary', 'significantly', '.'], answer: 'Reading improves your vocabulary significantly.', example: 'Reading improves your vocabulary significantly.' },
                { words: ['We', 'should', 'always', 'be', 'kind', 'to', 'others', '.'], answer: 'We should always be kind to others.', example: 'We should always be kind to others.' },
                { words: ['The', 'scientist', 'conducted', 'an', 'important', 'experiment', '.'], answer: 'The scientist conducted an important experiment.', example: 'The scientist conducted an important experiment.' },
                { words: ['Environmental', 'protection', 'is', 'very', 'important', 'today', '.'], answer: 'Environmental protection is very important today.', example: 'Environmental protection is very important today.' }
            ],
            expert: [
                { words: ['Technological', 'advancements', 'have', 'revolutionized', 'communication', '.'], answer: 'Technological advancements have revolutionized communication.', example: 'Technological advancements have revolutionized communication.' },
                { words: ['Global', 'cooperation', 'is', 'essential', 'for', 'peace', '.'], answer: 'Global cooperation is essential for peace.', example: 'Global cooperation is essential for peace.' },
                { words: ['The', 'researcher', 'analyzed', 'the', 'complex', 'data', '.'], answer: 'The researcher analyzed the complex data.', example: 'The researcher analyzed the complex data.' },
                { words: ['Sustainable', 'energy', 'sources', 'are', 'crucially', 'important', '.'], answer: 'Sustainable energy sources are crucially important.', example: 'Sustainable energy sources are crucially important.' }
            ]
        };
        
        // ================= MATH GAME DATA =================
        const mathData = {
            beginner: [
                { type: 'addition', problem: '2 + 3 = ?', answer: 5 },
                { type: 'addition', problem: '4 + 1 = ?', answer: 5 },
                { type: 'subtraction', problem: '5 - 2 = ?', answer: 3 },
                { type: 'subtraction', problem: '6 - 3 = ?', answer: 3 },
                { type: 'counting', problem: 'What comes after 2?', answer: 3 }
            ],
            intermediate: [
                { type: 'addition', problem: '12 + 8 = ?', answer: 20 },
                { type: 'subtraction', problem: '15 - 7 = ?', answer: 8 },
                { type: 'multiplication', problem: '3 × 4 = ?', answer: 12 },
                { type: 'division', problem: '12 ÷ 3 = ?', answer: 4 },
                { type: 'mixed', problem: '10 + 5 - 3 = ?', answer: 12 }
            ],
            advanced: [
                { type: 'multiplication', problem: '7 × 8 = ?', answer: 56 },
                { type: 'division', problem: '48 ÷ 6 = ?', answer: 8 },
                { type: 'addition', problem: '25 + 37 = ?', answer: 62 },
                { type: 'subtraction', problem: '50 - 23 = ?', answer: 27 },
                { type: 'mixed', problem: '4 × 5 + 6 = ?', answer: 26 }
            ],
            expert: [
                { type: 'multiplication', problem: '12 × 13 = ?', answer: 156 },
                { type: 'division', problem: '144 ÷ 12 = ?', answer: 12 },
                { type: 'addition', problem: '125 + 278 = ?', answer: 403 },
                { type: 'subtraction', problem: '500 - 237 = ?', answer: 263 },
                { type: 'mixed', problem: '15 × 3 - 20 = ?', answer: 25 }
            ]
        };
        
        // ================= INITIALIZE =================
        async function initGame() {
            console.log('Initializing game...');
            
            // Test database connection
            await testDBConnection();
            
            // Start session if connected
            if (gameState.dbConnected) {
                await startSession();
            }
            
            // Setup event listeners
            setupEvents();
            
            // Load first problem
            generateProblem();
            
            // Update UI
            updateStats();
            
            console.log('Game ready! Database:', gameState.dbConnected ? 'Connected' : 'Offline');
        }
        
        // ================= DATABASE FUNCTIONS =================
        async function testDBConnection() {
            try {
                console.log('Testing database connection...');
                const response = await fetch(`${API_URL}?endpoint=test_connection`);
                const data = await response.json();
                
                if (data.success) {
                    gameState.dbConnected = true;
                    document.getElementById('dbStatus').className = 'db-status db-connected';
                    document.getElementById('dbStatus').innerHTML = '<i class="fas fa-database"></i> Database Connected';
                    console.log('Database connection successful');
                } else {
                    throw new Error('Connection test failed');
                }
            } catch (error) {
                console.log('Database offline:', error.message);
                gameState.dbConnected = false;
                document.getElementById('dbStatus').className = 'db-status db-disconnected';
                document.getElementById('dbStatus').innerHTML = '<i class="fas fa-database"></i> Database Offline (Using Local Storage)';
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
                    console.log('Session started:', gameState.sessionId);
                }
            } catch (error) {
                console.log('Failed to start session:', error);
            }
        }
        
        async function saveQuestion(isCorrect, timeTaken, word) {
            if (!gameState.dbConnected || !gameState.sessionId) return;
            
            try {
                const data = {
                    session_id: gameState.sessionId,
                    game_type: gameState.currentGame,
                    difficulty: gameState.difficulty,
                    word: word || '',
                    is_correct: isCorrect,
                    time_taken: timeTaken
                };
                
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
                    updateStats();
                }
            } catch (error) {
                console.log('Failed to save question:', error);
            }
        }
        
        // ================= GAME LOGIC =================
        function setupEvents() {
            // Game buttons
            document.querySelectorAll('.game-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.dataset.game === 'mathadventure') {
                        return; // Let the onclick handle navigation
                    }
                    
                    document.querySelectorAll('.game-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    gameState.currentGame = this.dataset.game;
                    generateProblem();
                });
            });
            
            // Difficulty buttons
            document.querySelectorAll('.diff-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    gameState.difficulty = this.dataset.difficulty;
                    generateProblem();
                });
            });
            
            // Control buttons
            document.getElementById('submitBtn').addEventListener('click', checkAnswer);
            document.getElementById('nextBtn').addEventListener('click', generateProblem);
            document.getElementById('resetBtn').addEventListener('click', resetGame);
        }
        
        function resetGame() {
            gameState.score = 0;
            gameState.streak = 0;
            gameState.correct = 0;
            gameState.total = 0;
            gameState.selectedWords = [];
            gameState.currentSentence = [];
            
            updateStats();
            generateProblem();
        }
        
        function generateProblem() {
            // Clear feedback
            const feedback = document.getElementById('gameFeedback');
            feedback.className = 'feedback';
            feedback.textContent = 'Select your answer!';
            
            // Clear game display
            const gameDisplay = document.getElementById('gameDisplay');
            
            // Reset sentence builder state
            gameState.selectedWords = [];
            gameState.currentSentence = [];
            gameState.selectedOption = null;
            
            // Show default display
            document.getElementById('gameWord').style.display = 'block';
            document.getElementById('gameOptions').innerHTML = '';
            
            // Based on game type
            switch(gameState.currentGame) {
                case 'phonics':
                    generatePhonicsProblem();
                    break;
                case 'sightwords':
                    generateSightWordsProblem();
                    break;
                case 'vocabulary':
                    generateVocabularyProblem();
                    break;
                case 'spelling':
                    generateSpellingProblem();
                    break;
                case 'comprehension':
                    generateComprehensionProblem();
                    break;
                case 'sentencebuilder':
                    generateSentenceBuilderProblem();
                    break;
                case 'math':
                    generateMathProblem();
                    break;
            }
            
            // Update question
            updateQuestionText();
        }
        
        // ================= ORIGINAL GAMES =================
        function generatePhonicsProblem() {
            const letters = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M'];
            const sounds = ['buh', 'kuh', 'duh', 'fuh', 'guh', 'huh', 'juh', 'kuh', 'luh', 'muh'];
            
            const index = Math.floor(Math.random() * letters.length);
            const letter = letters[index];
            const correctSound = sounds[index];
            
            // Get wrong sounds
            const wrongSounds = [];
            while (wrongSounds.length < 3) {
                const wrongIndex = Math.floor(Math.random() * sounds.length);
                if (wrongIndex !== index && !wrongSounds.includes(sounds[wrongIndex])) {
                    wrongSounds.push(sounds[wrongIndex]);
                }
            }
            
            // Combine and shuffle
            const options = [correctSound, ...wrongSounds].sort(() => Math.random() - 0.5);
            const correctIndex = options.indexOf(correctSound);
            
            gameState.currentProblem = {
                type: 'phonics',
                letter: letter,
                correctAnswer: correctSound,
                options: options,
                correctIndex: correctIndex
            };
            
            // Update display
            document.getElementById('gameWord').textContent = letter;
            
            // Create options
            const optionsContainer = document.getElementById('gameOptions');
            options.forEach((option, i) => {
                const button = document.createElement('button');
                button.className = 'option-btn';
                button.textContent = option;
                button.dataset.index = i;
                button.addEventListener('click', function() {
                    // Remove selected from all
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    // Add selected to clicked
                    this.classList.add('selected');
                    gameState.selectedOption = i;
                });
                optionsContainer.appendChild(button);
            });
        }
        
        function generateSightWordsProblem() {
            const words = ['the', 'and', 'you', 'that', 'was', 'for', 'are', 'with', 'his', 'they'];
            const word = words[Math.floor(Math.random() * words.length)];
            
            // Get wrong words
            const wrongWords = [];
            while (wrongWords.length < 3) {
                const wrongWord = words[Math.floor(Math.random() * words.length)];
                if (wrongWord !== word && !wrongWords.includes(wrongWord)) {
                    wrongWords.push(wrongWord);
                }
            }
            
            // Combine and shuffle
            const options = [word, ...wrongWords].sort(() => Math.random() - 0.5);
            const correctIndex = options.indexOf(word);
            
            gameState.currentProblem = {
                type: 'sightwords',
                word: word,
                correctAnswer: word,
                options: options,
                correctIndex: correctIndex
            };
            
            // Update display
            document.getElementById('gameWord').textContent = word;
            
            // Create options
            const optionsContainer = document.getElementById('gameOptions');
            options.forEach((option, i) => {
                const button = document.createElement('button');
                button.className = 'option-btn';
                button.textContent = option;
                button.dataset.index = i;
                button.addEventListener('click', function() {
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    gameState.selectedOption = i;
                });
                optionsContainer.appendChild(button);
            });
        }
        
        function generateVocabularyProblem() {
            const vocabulary = [
                { word: 'cat', meaning: 'A small furry animal' },
                { word: 'dog', meaning: 'A friendly animal' },
                { word: 'sun', meaning: 'Bright star for light' },
                { word: 'book', meaning: 'Something to read' }
            ];
            
            const item = vocabulary[Math.floor(Math.random() * vocabulary.length)];
            
            // Get wrong meanings
            const wrongMeanings = [];
            while (wrongMeanings.length < 3) {
                const wrongItem = vocabulary[Math.floor(Math.random() * vocabulary.length)];
                if (wrongItem.word !== item.word && !wrongMeanings.includes(wrongItem.meaning)) {
                    wrongMeanings.push(wrongItem.meaning);
                }
            }
            
            // Combine and shuffle
            const options = [item.meaning, ...wrongMeanings].sort(() => Math.random() - 0.5);
            const correctIndex = options.indexOf(item.meaning);
            
            gameState.currentProblem = {
                type: 'vocabulary',
                word: item.word,
                meaning: item.meaning,
                correctAnswer: item.meaning,
                options: options,
                correctIndex: correctIndex
            };
            
            // Update display
            document.getElementById('gameWord').textContent = item.word;
            
            // Create options
            const optionsContainer = document.getElementById('gameOptions');
            options.forEach((option, i) => {
                const button = document.createElement('button');
                button.className = 'option-btn';
                button.textContent = option;
                button.dataset.index = i;
                button.addEventListener('click', function() {
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    gameState.selectedOption = i;
                });
                optionsContainer.appendChild(button);
            });
        }
        
        function generateSpellingProblem() {
            const words = ['cat', 'dog', 'sun', 'book', 'ball', 'hat', 'pen', 'cup'];
            const word = words[Math.floor(Math.random() * words.length)];
            
            gameState.currentProblem = {
                type: 'spelling',
                word: word,
                correctAnswer: word
            };
            
            // Update display
            document.getElementById('gameWord').textContent = word;
            
            // Create input field instead of options
            const optionsContainer = document.getElementById('gameOptions');
            optionsContainer.innerHTML = `
                <input type="text" class="input-field" id="spellingInput" placeholder="Type the word here" autofocus>
            `;
            
            // Focus on input
            setTimeout(() => {
                document.getElementById('spellingInput').focus();
            }, 100);
        }
        
        function generateComprehensionProblem() {
            const passages = [
                {
                    passage: "Tom has a red ball. He plays with it in the park.",
                    question: "What color is Tom's ball?",
                    options: ["Blue", "Red", "Green", "Yellow"],
                    answer: 1
                },
                {
                    passage: "The cat sits on the mat. It likes to sleep.",
                    question: "Where is the cat?",
                    options: ["On bed", "On mat", "On chair", "On table"],
                    answer: 1
                },
                {
                    passage: "Sara reads a book every day. She likes stories.",
                    question: "What does Sara do every day?",
                    options: ["Watch TV", "Read a book", "Play games", "Cook food"],
                    answer: 1
                }
            ];
            
            // Get random passage
            const passage = passages[Math.floor(Math.random() * passages.length)];
            
            gameState.currentProblem = {
                type: 'comprehension',
                passage: passage.passage,
                question: passage.question,
                options: passage.options,
                correctAnswer: passage.options[passage.answer],
                correctIndex: passage.answer
            };
            
            // Update display
            document.getElementById('gameWord').style.display = 'none';
            
            const displayHTML = `
                <div class="reading-passage">
                    ${passage.passage}
                </div>
                <div style="margin: 20px 0; font-size: 1.3rem;">
                    ${passage.question}
                </div>
            `;
            
            // Update the question area
            const gameQuestion = document.getElementById('gameQuestion');
            gameQuestion.innerHTML = displayHTML;
            
            // Create options
            const optionsContainer = document.getElementById('gameOptions');
            optionsContainer.innerHTML = '';
            
            passage.options.forEach((option, i) => {
                const button = document.createElement('button');
                button.className = 'option-btn';
                button.textContent = option;
                button.dataset.index = i;
                button.addEventListener('click', function() {
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    gameState.selectedOption = i;
                });
                optionsContainer.appendChild(button);
            });
        }
        
        // ================= SENTENCE BUILDER (FIXED VERSION) =================
        function generateSentenceBuilderProblem() {
            // Hide the word display
            document.getElementById('gameWord').style.display = 'none';
            
            // Get sentence data for current difficulty
            const sentences = sentenceData[gameState.difficulty] || sentenceData.beginner;
            const sentenceObj = sentences[Math.floor(Math.random() * sentences.length)];
            
            // Create shuffled words for display
            const shuffledWords = [...sentenceObj.words];
            shuffleArray(shuffledWords);
            
            // Store the words array separately (without punctuation attached to other words)
            // But keep the original correct answer for comparison
            gameState.currentProblem = {
                type: 'sentencebuilder',
                words: sentenceObj.words,
                shuffledWords: shuffledWords,
                correctAnswer: sentenceObj.answer,
                example: sentenceObj.example,
                // Store a normalized version for comparison
                normalizedAnswer: sentenceObj.answer.trim()
            };
            
            // Create the display
            const displayHTML = `
                <div class="sentence-builder-container">
                    <h3 style="color: #5d8aa8; margin-bottom: 10px;">
                        <i class="fas fa-align-left"></i> Sentence Builder
                    </h3>
                    
                    <div class="sentence-builder-instruction">
                        Build a sentence with these words:
                    </div>
                    
                    <div class="sentence-example">
                        Example: <strong>${sentenceObj.example}</strong>
                    </div>
                    
                    <div class="sentence-display-area" id="sentenceDisplay">
                        <span id="emptySentenceMessage" style="color: #888; font-style: italic;">
                            Your sentence will appear here...
                        </span>
                    </div>
                    
                    <div class="word-bank" id="wordBankContainer">
                        <!-- Word tokens will be added by JavaScript -->
                    </div>
                    
                    <button class="clear-sentence-btn" id="clearSentenceBtn">
                        <i class="fas fa-eraser"></i> Clear Sentence
                    </button>
                </div>
            `;
            
            // Update display
            const gameQuestion = document.getElementById('gameQuestion');
            gameQuestion.innerHTML = displayHTML;
            document.getElementById('gameOptions').innerHTML = '';
            
            // Populate word bank
            const wordBankContainer = document.getElementById('wordBankContainer');
            wordBankContainer.innerHTML = '';
            
            shuffledWords.forEach((word, index) => {
                const wordElement = document.createElement('div');
                wordElement.className = 'word-token';
                wordElement.textContent = word;
                wordElement.dataset.word = word;
                wordElement.dataset.index = index;
                
                wordElement.addEventListener('click', function() {
                    // Check if this word is already used
                    const usedCount = gameState.selectedWords.filter(w => w === word).length;
                    const availableCount = shuffledWords.filter(w => w === word).length;
                    
                    if (usedCount < availableCount) {
                        gameState.selectedWords.push(word);
                        gameState.currentSentence.push(word);
                        updateSentenceDisplay();
                        
                        // Mark this specific token as used
                        this.classList.add('used');
                        this.style.pointerEvents = 'none';
                    }
                });
                
                wordBankContainer.appendChild(wordElement);
            });
            
            // Add event listener for clear button
            document.getElementById('clearSentenceBtn').addEventListener('click', clearSentence);
            
            // Update button text
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check"></i> Check Sentence';
        }
        
        // ================= MATH GAME =================
        function generateMathProblem() {
            // Hide the word display
            document.getElementById('gameWord').style.display = 'none';
            
            // Get math data for current difficulty
            const problems = mathData[gameState.difficulty] || mathData.beginner;
            const problem = problems[Math.floor(Math.random() * problems.length)];
            
            gameState.currentProblem = {
                type: 'math',
                problem: problem.problem,
                answer: problem.answer,
                mathType: problem.type
            };
            
            // Create the display
            const displayHTML = `
                <div class="math-container">
                    <h3 style="color: #ff5e62; margin-bottom: 10px;">
                        <i class="fas fa-calculator"></i> Math Challenge
                    </h3>
                    
                    <div class="sentence-builder-instruction">
                        Solve the math problem:
                    </div>
                    
                    <div class="math-problem" id="mathProblem">
                        ${problem.problem}
                    </div>
                    
                    <input type="number" class="math-input" id="mathInput" placeholder="Enter answer" autofocus>
                </div>
            `;
            
            // Update display
            const gameQuestion = document.getElementById('gameQuestion');
            gameQuestion.innerHTML = displayHTML;
            document.getElementById('gameOptions').innerHTML = '';
            
            // Update button text
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check"></i> Check Answer';
            
            // Focus on input
            setTimeout(() => {
                document.getElementById('mathInput').focus();
            }, 100);
        }
        
        // Helper function for sentence builder
        function updateSentenceDisplay() {
            const sentenceDisplay = document.getElementById('sentenceDisplay');
            const emptyMessage = document.getElementById('emptySentenceMessage');
            
            if (gameState.currentSentence.length === 0) {
                sentenceDisplay.innerHTML = '<span id="emptySentenceMessage" style="color: #888; font-style: italic;">Your sentence will appear here...</span>';
                return;
            }
            
            // Remove empty message
            if (emptyMessage) {
                emptyMessage.remove();
            }
            
            // Clear and rebuild sentence display
            sentenceDisplay.innerHTML = '';
            
            gameState.currentSentence.forEach((word, index) => {
                const wordSpan = document.createElement('span');
                wordSpan.className = 'sentence-word';
                wordSpan.textContent = word;
                wordSpan.style.animation = 'popIn 0.3s ease';
                sentenceDisplay.appendChild(wordSpan);
            });
        }
        
        function clearSentence() {
            gameState.selectedWords = [];
            gameState.currentSentence = [];
            
            // Reset all word tokens
            const wordTokens = document.querySelectorAll('.word-token');
            wordTokens.forEach(token => {
                token.classList.remove('used');
                token.style.pointerEvents = 'auto';
            });
            
            // Reset sentence display
            updateSentenceDisplay();
            
            // Clear feedback
            const feedback = document.getElementById('gameFeedback');
            feedback.className = 'feedback';
            feedback.textContent = 'Click words to build a sentence!';
        }
        
        // Helper function to build the user's sentence properly
        function buildUserSentence() {
            if (gameState.currentSentence.length === 0) return '';
            
            let sentence = '';
            
            for (let i = 0; i < gameState.currentSentence.length; i++) {
                const word = gameState.currentSentence[i];
                
                // Add the word
                sentence += word;
                
                // Add space unless it's punctuation or last word
                if (i < gameState.currentSentence.length - 1) {
                    const nextWord = gameState.currentSentence[i + 1];
                    // Don't add space before punctuation
                    if (nextWord !== '.' && nextWord !== ',' && nextWord !== '!' && nextWord !== '?') {
                        sentence += ' ';
                    }
                }
            }
            
            return sentence;
        }
        
        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }
        
        function updateQuestionText() {
            // Only update for original games
            if (gameState.currentGame === 'comprehension' || gameState.currentGame === 'sentencebuilder' || gameState.currentGame === 'math') {
                return;
            }
            
            const questions = {
                'phonics': 'What sound does this letter make?',
                'sightwords': 'Read this word:',
                'vocabulary': 'What does this word mean?',
                'spelling': 'Spell this word:'
            };
            
            document.getElementById('gameQuestion').textContent = questions[gameState.currentGame] || 'Answer the question:';
            document.getElementById('gameWord').style.display = 'block';
        }
        
        async function checkAnswer() {
            let isCorrect = false;
            const startTime = Date.now();
            let wordToSave = '';
            
            // Handle sentence builder differently
            if (gameState.currentGame === 'sentencebuilder') {
                // Build the user's sentence properly
                const userSentence = buildUserSentence();
                wordToSave = userSentence.substring(0, 50);
                
                if (!userSentence || gameState.currentSentence.length === 0) {
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = 'Please build a sentence first!';
                    return;
                }
                
                // Compare the normalized sentences
                const userSentenceNormalized = userSentence.trim();
                const correctAnswerNormalized = gameState.currentProblem.normalizedAnswer;
                
                isCorrect = userSentenceNormalized === correctAnswerNormalized;
                
                if (isCorrect) {
                    gameState.score += 20;
                    gameState.streak++;
                    gameState.correct++;
                    
                    document.getElementById('gameFeedback').className = 'feedback correct';
                    document.getElementById('gameFeedback').textContent = `Perfect! +20 points! You built: "${userSentence}"`;
                } else {
                    gameState.streak = 0;
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = `Try again! Your sentence: "${userSentence}". Correct: "${correctAnswerNormalized}"`;
                }
                
                // Disable all word tokens
                const wordTokens = document.querySelectorAll('.word-token');
                wordTokens.forEach(token => {
                    token.style.pointerEvents = 'none';
                });
                
                // Increment total for sentence builder
                gameState.total++;
            }
            // Handle math game
            else if (gameState.currentGame === 'math') {
                const input = document.getElementById('mathInput');
                const userAnswer = parseFloat(input.value);
                wordToSave = gameState.currentProblem.problem;
                
                if (isNaN(userAnswer)) {
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = 'Please enter a number!';
                    return;
                }
                
                isCorrect = Math.abs(userAnswer - gameState.currentProblem.answer) < 0.001;
                
                if (isCorrect) {
                    gameState.score += 15;
                    gameState.streak++;
                    gameState.correct++;
                    
                    document.getElementById('gameFeedback').className = 'feedback correct';
                    document.getElementById('gameFeedback').textContent = `Excellent! +15 points! ${gameState.currentProblem.problem} ${gameState.currentProblem.answer}`;
                } else {
                    gameState.streak = 0;
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = `Try again! The answer is: ${gameState.currentProblem.answer}`;
                }
                
                // Disable input
                input.disabled = true;
                gameState.total++;
            }
            // Handle comprehension
            else if (gameState.currentGame === 'comprehension') {
                if (gameState.selectedOption === null) {
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = 'Please select an answer first!';
                    return;
                }
                isCorrect = gameState.selectedOption === gameState.currentProblem.correctIndex;
                wordToSave = 'Comprehension: ' + gameState.currentProblem.question.substring(0, 20);
            }
            // Handle spelling
            else if (gameState.currentGame === 'spelling') {
                const input = document.getElementById('spellingInput');
                const userAnswer = input.value.toLowerCase().trim();
                isCorrect = userAnswer === gameState.currentProblem.word.toLowerCase();
                wordToSave = gameState.currentProblem.word;
            }
            // Handle other games
            else {
                if (gameState.selectedOption === null) {
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    document.getElementById('gameFeedback').textContent = 'Please select an answer first!';
                    return;
                }
                isCorrect = gameState.selectedOption === gameState.currentProblem.correctIndex;
                wordToSave = gameState.currentProblem.word || gameState.currentProblem.letter;
            }
            
            // Calculate time taken
            const timeTaken = (Date.now() - startTime) / 1000;
            
            // Save to database
            await saveQuestion(isCorrect, timeTaken, wordToSave);
            
            // Update game state for non-sentence builder and non-math games
            if (gameState.currentGame !== 'sentencebuilder' && gameState.currentGame !== 'math') {
                gameState.total++;
                if (isCorrect) {
                    gameState.score += 10;
                    gameState.streak++;
                    gameState.correct++;
                    
                    document.getElementById('gameFeedback').className = 'feedback correct';
                    document.getElementById('gameFeedback').textContent = `Correct! +10 points!`;
                } else {
                    gameState.streak = 0;
                    document.getElementById('gameFeedback').className = 'feedback incorrect';
                    if (gameState.currentGame === 'comprehension') {
                        document.getElementById('gameFeedback').textContent = `Incorrect! The answer was: ${gameState.currentProblem.correctAnswer}`;
                    } else if (gameState.currentGame === 'spelling') {
                        document.getElementById('gameFeedback').textContent = `Incorrect! The word is: ${gameState.currentProblem.word}`;
                    } else {
                        document.getElementById('gameFeedback').textContent = `Incorrect! The answer was: ${gameState.currentProblem.correctAnswer}`;
                    }
                }
                
                // Disable buttons
                document.querySelectorAll('.option-btn').forEach(btn => {
                    btn.disabled = true;
                });
                if (document.getElementById('spellingInput')) {
                    document.getElementById('spellingInput').disabled = true;
                }
            }
            
            // Update stats
            updateStats();
        }
        
        function updateStats() {
            document.getElementById('scoreValue').textContent = gameState.score;
            document.getElementById('streakValue').textContent = gameState.streak;
            document.getElementById('correctValue').textContent = `${gameState.correct}/${gameState.total}`;
            document.getElementById('dbStats').textContent = `${gameState.savedCount} saved`;
        }
        
        // ================= START GAME =================
        window.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>