<?php
/**
 * Installer Class
 */
class WeDevs_Notification_Installer {

    /**
     * Binding all events
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        register_activation_hook( WP_NOTIFY_FILE, array( $this, 'activate_notify_now' ) );
        register_deactivation_hook( WP_NOTIFY_FILE, array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 1.0.0
     */
    public function activate_notify_now() {
        $this->create_notify_tables();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Create necessary table for notification
     *
     * @since 1.0.0
     *
     * @return  void
     */
    public function create_notify_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = [

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}notifications` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `user_id` int(11) unsigned NOT NULL,
                 `provider` varchar(255) NOT NULL,
                 `event` varchar(255) DEFAULT '',
                 `message` text NOT NULL,
                 `link` varchar(255) NULL,                 
                 `read` tinyint(1) NOT NULL,
                 `type` varchar(255) NOT NULL,
                 `sent` datetime DEFAULT NULL,
                 `expires` datetime DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `user_id` (`user_id`)
             ) $collate;"

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

    }

}

