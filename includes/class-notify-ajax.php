<?php
namespace WeDevs\Notification;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    /**
     * Bind all the ajax event
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_notification_read', [ $this, 'change_notification_to_read' ] );
        add_action( 'wp_ajax_mark_all_read', [ $this, 'mark_all_as_read' ] );
    }

    /**
    * Change notification status to read
    *
    * @return bool
    */
    public function change_notification_to_read() {
        if ( isset( $_POST['id'] ) ) {
            $read = true;
            $handler = new \WeDevs\Notification\Notify_Handler();
            $handler->change_notification_status( intval( $_POST['id'] ), $read );

            $this->send_success( __( 'Updated successfully', 'notification-center' ) );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * Change notification status to read
    *
    * @return bool
    */
    public function mark_all_as_read() {
        global $wpdb;

        $wpdb->update( $wpdb->prefix .'notifications',
            array( 'read' => true ),
            array( 'read' => false, 'user_id' => get_current_user_id() )
        );

        $this->send_success( __( 'Updated successfully', 'notification-center' ) );
    }

}