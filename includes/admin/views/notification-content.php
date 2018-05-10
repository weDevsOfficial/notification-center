<div id="wp-notification-wrap">
    <div class="notification-header-wrap">
        <header class="notification-header">
            <div class="header-area">
                <h1>Notifications <small class="notification-count"><?php echo $count_notifications; ?></small></h1>
            </div>
            <div class="read-area">
                <a href="#">Mark all as read</a>
            </div>
        </header>
    </div>
    <section class="notification-content">
        <?php echo $notifications_container; ?>
    </section>
</div>
