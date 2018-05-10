<?php
namespace WeDevs\Notification;

class Notify_Handler {

    protected $notifications;

    public function __construct() {
        $this->notifications = [];

        $this->get_provider(null);
        
        if ( is_admin() ) {
            add_action( 'admin_bar_menu', [ $this, 'modify_admin_bar_menu' ] );
        }

        // For Test purpose
        add_filter( 'notification_providers', [ $this, 'custom_notification_providers'] );
    }

    // For Test purpose
    public function custom_notification_providers( $providers ) {
        $providers['wpuf'] = [
            'logo'    => 'https://image.flaticon.com/icons/svg/163/163813.svg',
            'data' => [
                'author'   => 'WP User Frontend',
                'provider' => 'WPUF',
                'email'    => 'wpuf@dokan.com'
            ]
        ];

        return $providers;
    }

    /**
     * Set default providers
     *
     * @return void
     */
    public function get_provider( $provider ) {
        $providers = [
            'dokan' => [
                'logo'    => 'https://image.flaticon.com/icons/svg/163/163811.svg',
                'data' => [
                    'author'   => 'Dokan Multivendor',
                    'provider' => 'Dokan',
                    'email'    => 'info@dokan.com'
                ]
            ],
            'erp' => [
                'logo'    => 'https://image.flaticon.com/icons/svg/163/163809.svg',
                'data' => [
                    'author'   => 'Johnson Corporation',
                    'provider' => 'WP ERP',
                    'email'    => 'info@Johncorp.com'
                ]
            ]
        ];

        $providers = apply_filters( 'notification_providers', $providers );

        if ( array_key_exists( $provider, $providers ) ) {
            return $providers[$provider];
        }

        $default = [
            'logo'    => '',
            'data' => [
                'author'   => '',
                'provider' => '',
                'email'    => ''
            ]
        ];

        return $default;
        
    }

    /**
     * Admin bar menu hook
     *
     * @return void
     */
    public function modify_admin_bar_menu() {
        if ( ! is_admin_bar_showing() ) {
            return;
        }

        $this->get_notifications( get_current_user_id() );

        $this->add_notification_list();

    }

    /**
     * Add notification to the list
     *
     * @return void
     */
    public function add_notification_list() {
        $title = '<span class="ab-icon"></span>';
        $title .= '<span class="notif-count"><span class="notif-value">' . count($this->notifications) . '</span></span>';

        $notifications_container = '<ul class="notifications">';

        foreach ( $this->notifications as $notification ) {
            $id            = intval( $notification['id'] );
            $read_class    = intval( $notification['read'] ) ? 'read' : 'unread';
            $avatar        = $notification['avatar'];
            $notif_message = ' <strong>' . $notification['provider']
                            . ' </strong>' . $notification['event']
                            . ' ' . $notification['message'];
            $notif_link =  $notification['link'];
            $notif_sent = new \DateTime( $notification['sent'] );

            $notifications_container .= <<<EOT
            <li class="{$read_class} notify-list" data-id="{$id}">
                <a href="#">
                    <div class="notif-item">
                        <div class="notif-icon">
                            <img src="{$avatar}" alt="icon">
                        </div>
                        <div class="notif-message-wrap">
                            <div class="notif-message">
                                {$notif_message}
                            </div>
                            <div class="notif-time">
                                {$notif_sent->format('F j \a\t H:ia')}
                            </div>
                        </div>
                    </div>
                </a>
            </li>
EOT;
        }

        $notifications_container .= '</ul>';

        $this->notifications_in_admin_bar( $title, $notifications_container );
    }

    /**
     * Show notifications in admin bar
     *
     * @param String $title
     * @param Array $notifications
     * @param String $notifications_container
     * 
     * @return void
     */
    public function notifications_in_admin_bar($title, $notifications_container) {
        global $wp_admin_bar;

        $count_notifications = count($this->notifications);

        ob_start();
        include_once WP_NOTIFY_VIEWS . '/notification-content.php';
        $content = ob_get_clean();

        $wp_admin_bar->add_menu( [
            'id'     => 'notification-center',
            'parent' => 'top-secondary',
            'title'  => $title,
            'href'   => '#',
            'meta'   => [
                'class'   => 'wp-notification-center',
                'onclick' => 'nc_toggle_notification()',
                'html'    => $content
            ]
        ] );
    }

    /**
     * Add notification
     *
     * @param Array $user_ids
     * @param String $provider
     * @param String $event
     * @param String $message
     * @param String $link
     * @param String $type
     * @param String $sent
     * @param String $expires
     * @return void
     */
    public function add_notification( $user_ids, $provider, $event, $message, $link, $type, $sent, $expires ) {
        global $wpdb;

        $table = $wpdb->prefix . 'notifications';

        // sanitize
        $message = wp_kses( $message, array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array()
        ) );

        foreach ( $user_ids as $user_id ) {
            $wpdb->insert( 
                $table,
                array( 
                    'user_id'  => $user_id,
                    'provider' => $provider,
                    'event'    => $event,
                    'message'  => $message,
                    'link'     => $link,
                    'type'     => $type,
                    'sent'     => $sent,
                    'expires'  => $expires
                ), 
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) 
            );
        }

    }

    public function get_notifications( $user_id = null ) {
        global $wpdb;

        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}notifications WHERE user_id = $user_id", OBJECT );  

        foreach ( $results as $result ) {
            $provider = $this->get_provider( $result->provider );

            $notification = [
                'id'       => $result->id,
                'provider' => $result->provider,
                'avatar'   => $provider['logo'],
                'message'  => $result->message,
                'link'     => $result->link,
                'event'    => $result->event,
                'read'     => $result->read,
                'sent'     => $result->sent,
                'expires'  => $result->expires,
                'meta'     => $provider['data']
            ];

            array_push( $this->notifications, $notification );
        }

        return $this->notifications;
    }

    public function change_notification_status( $id, $status ) {
        global $wpdb;
        
        return $wpdb->update( $wpdb->prefix .'notifications',
            array( 'read' => $status ), array( 'id' => $id ), array( '%d' )
        );
    }

}

