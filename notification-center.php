<?php
/*
Plugin Name: Notification Center
Plugin URI: http://wperp.com/downloads/notification-manager/
Description: A central notification system for WordPress
Version: 1.0.0
Author: weDevs
Author URI: https://wedevs.com
Text Domain: notification-center
Domain Path: languages
License: GPL2
*/

/**
 * Copyright (c) 2016 weDevs (email: info@wperp.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined('ABSPATH') ) exit;

/**
 * Base_Plugin class
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
class WeDevs_ERP_Notification {

    /**
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */

    /* plugin version 1.0.0
    *
    * @var string
    */
    public $version = '1.0.0';

    public function __construct() {        
        $this->define_constants();
        $this->setup_database();     

        add_action( 'init', array( $this, 'init_plugin' ) );
    }

    public function setup_database() {
        require_once WP_NOTIFY_INCLUDES . '/class-notify-install.php';

        new WeDevs_Notification_Installer;
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();
        $this->init_classes();
        $this->actions();
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new WeDevs_ERP_Notification();
        }

        return $instance;
    }

    /**
     * check php version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare(PHP_VERSION, '5.4.0', '<=') ) {
            return false;
        }

        return true;
    }

    /**
     * define the plugin constant
     *
     * @return void
     */
    public function define_constants() {
        define( 'WP_NOTIFY', $this->version );
        define( 'WP_NOTIFY_FILE', __FILE__ );
        define( 'WP_NOTIFY_PATH', dirname( WP_NOTIFY_FILE ) );
        define( 'WP_NOTIFY_INCLUDES', WP_NOTIFY_PATH . '/includes' );
        define( 'WP_NOTIFY_URL', plugins_url( '', WP_NOTIFY_FILE ) );
        define( 'WP_NOTIFY_ASSETS', WP_NOTIFY_URL . '/assets' );
        define( 'WP_NOTIFY_VIEWS', WP_NOTIFY_INCLUDES . '/admin/views' );        
    }

    /**
     * including necessary files
     *
     * @return void
     */
    public function includes() {
        if ( ! $this->is_supported_php() ) {
            return;
        }

        require_once WP_NOTIFY_INCLUDES . '/traits/Ajax.php';
        require_once WP_NOTIFY_INCLUDES . '/class-notify-ajax.php';
        require_once WP_NOTIFY_INCLUDES . '/class-notify-handler.php';
        require_once WP_NOTIFY_INCLUDES . '/api/class-notification-controller.php';
    }

    /**
     * function objective
     *
     * @return
     */
    public function init_classes() {
        new WeDevs\Notification\Ajax_Handler;
        new WeDevs\Notification\Notify_Handler;
    }

    /**
     * function actions
     *
     * @return
     */
    public function actions() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'rest_api_init', [ $this, 'load_notify_api_controller' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'notification-center', false, dirname( plugin_basename(__FILE__) ) . '/i18n/languages/' );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @since 1.0.0
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'notify-style', WP_NOTIFY_ASSETS . '/css/stylesheet.css' );
        wp_enqueue_script( 'notify-script', WP_NOTIFY_ASSETS . '/js/notification-center.js', array('jquery'), false, true );

        $localize_scripts = [
            'nonce'    => wp_create_nonce('doc_form_builder_nonce'),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),

            'current_user_id'      => get_current_user_id(),
            'isAdmin'              => current_user_can( 'manage_options' )
        ];

        wp_localize_script( 'notify-script', 'wpNotifyCenter', $localize_scripts );
    }

    /**
     * Register api files
     * @since 1.0.0
     *
     * @param $controllers
     *
     * @return array
     */
    public function load_notify_api_controller() {
        $controller = new \WeDevs\API\Notification_Controller;
        $controller->register_routes();
    }

}

WeDevs_ERP_Notification::init();

