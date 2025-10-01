<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header">
    <div class="container">
        <nav class="navbar">
            <a href="<?= SITE_URL ?>/index.php" class="logo">
                <i class="fas fa-globe-africa"></i> Iburunga
            </a>
            
            <ul class="nav-menu">
                <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li><a href="<?= SITE_URL ?>/tours.php">Tours</a></li>
                <li><a href="<?= SITE_URL ?>/about.php">About</a></li>
                <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                <li><a href="<?= SITE_URL ?>/faqs.php">FAQs</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-dashboard"></i> Dashboard
                        </a></li>
                    <?php else: ?>
                        <li><a href="<?= SITE_URL ?>/my-bookings.php">My Bookings</a></li>
                    <?php endif; ?>
                    <li><a href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Sign Up</a></li>
                <?php endif; ?>
            </ul>
            
            <button class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </div>
</header>
