<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd Games - Learn Through Play</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@700&family=Fredoka+One&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/landing.css">
</head>

<body>
    <div class="container">

        <?php include 'navbar.php'; ?>

        <div class="hero-section">
            <i class="fas fa-shapes shape shape-1"></i>
            <i class="fas fa-star shape shape-2"></i>
            <i class="fas fa-puzzle-piece shape shape-3"></i>
            <i class="fas fa-gamepad shape shape-4"></i>

            <div class="hero-content">
                <h1 class="hero-title">Welcome to DepEd Games!</h1>
                <p class="hero-subtitle">
                    Embark on an exciting journey of learning! Practice reading, spelling, and math skills through fun
                    and interactive games designed just for you.
                </p>

                <div class="game-cards-container">
                    <a href="reading_adventure.php" class="landing-card reading-card">
                        <div class="card-image">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="card-content">
                            <h2 class="card-title">Reading Adventure</h2>
                            <p class="card-desc">Master phonics, sight words, and spelling with our interactive reading
                                challenges.</p>
                            <span class="card-btn">Play Now</span>
                        </div>
                    </a>

                    <a href="math_adventure.php" class="landing-card math-card">
                        <div class="card-image">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="card-content">
                            <h2 class="card-title">Math Adventure</h2>
                            <p class="card-desc">Sharpen your math skills with addition, subtraction, multiplication,
                                and more!</p>
                            <span class="card-btn">Play Now</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="analytics-banner">
            <i class="fas fa-chart-line shape shape-1" style="opacity: 0.15"></i>
            <div class="analytics-content">
                <h2>Track Your Progress</h2>
                <p>See how much you've learned and check your scores!</p>
                <a href="analytics.php" class="analytics-btn">
                    <i class="fas fa-chart-pie"></i> View Analytics
                </a>
            </div>
        </div>

    </div>
</body>

</html>