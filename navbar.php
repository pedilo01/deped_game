<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="css/navbar.css">

<nav class="main-navbar">
    <a href="index.php" class="nav-brand">
        <i class="fas fa-shapes"></i> DepEd Games
    </a>

    <div class="nav-links">
        <a href="index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Home
        </a>

        <a href="reading_adventure.php"
            class="nav-item <?php echo $current_page == 'reading_adventure.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Reading
        </a>

        <a href="math_adventure.php"
            class="nav-item math <?php echo $current_page == 'math_adventure.php' ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i> Math
        </a>

        <a href="analytics.php"
            class="nav-item analytics <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i> Analytics
        </a>
    </div>
</nav>