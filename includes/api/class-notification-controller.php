<?php
namespace WeDevs\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class Notification_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'notifications';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'v1';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_notifications' ],
                'permission_callback' => function ( $request ) {
                    return is_user_logged_in();
                }
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'add_notification' ],
                'permission_callback' => function ( $request ) {
                    return is_user_logged_in();
                }
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<notification_id>[\d]+)/read', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'make_notification_read' ],
                'permission_callback' => function ( $request ) {
                    return is_user_logged_in();
                }
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<notification_id>[\d]+)/unread', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'make_notification_unread' ],
                'permission_callback' => function ( $request ) {
                    return is_user_logged_in();
                }
            ]
        ] );
    }

    /**
     * Get notifications
     *
     * @since 1.0.0
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_notifications( $request ) {
        if ( intval( $request['user_id'] ) != get_current_user_id() ) {
            return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $handler = new \WeDevs\Notification\Notify_Handler();
        $notifications = $handler->get_notifications( intval( $request[ 'user_id' ] ) );
        
        $response = rest_ensure_response( $notifications );

        return $response;
    }

    /**
     * Add notification
     *
     * @param [type] $request
     * @return void
     */
    public function add_notification( $request ) {
        $handler = new \WeDevs\Notification\Notify_Handler();
        $result = $handler->add_notification( 
            explode(',', $request['user_ids']),
            $request['provider'],
            $request['event'],
            $request['message'],
            $request['link'],
            $request['type'],
            $request['start'],
            $request['expires']
        );

        $response = rest_ensure_response( 'created' );

        return $response;
    }

    /**
     * Change notification status to read
     *
     * @param [type] $request
     * @return void
     */
    public function make_notification_read( $request ) {
        $read = true;

        $handler = new \WeDevs\Notification\Notify_Handler();
        $handler->change_notification_status( intval( $request['notification_id'] ), $read );

        $response = rest_ensure_response( 'read' );

        return $response;
    }

    /**
     * Change notification status to unread
     *
     * @param [type] $request
     * @return void
     */
    public function make_notification_unread( $request ) {
        $read = false;

        $handler = new \WeDevs\Notification\Notify_Handler();
        $handler->change_notification_status( intval( $request['notification_id'] ), $read );

        $response = rest_ensure_response( 'unread' );

        return $response;
    }

}
