<?php
$db = Database::getInstance();
$pendingBookings = $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];
?>
<div class="topbar">
    <div class="topbar-left">
        <h2><?= ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))) ?></h2>
    </div>
    
    <div class="topbar-right">
        <div class="notification-icon">
            <i class="fas fa-bell"></i>
            <?php if ($pendingBookings > 0): ?>
                <span class="notification-badge"><?= $pendingBookings ?></span>
            <?php endif; ?>
        </div>
        
        <div class="user-menu">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
            </div>
            <div class="user-info">
                <strong><?= $_SESSION['name'] ?></strong>
                <small>Administrator</small>
            </div>
        </div>
    </div>
</div>
