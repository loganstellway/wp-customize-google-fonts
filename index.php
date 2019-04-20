<?php

/**
 * Plugin Name: Logan Stellway - Google Fonts for Customize
 * Plugin URI: www.loganstellway.com
 * Description: Add Google Fonts components to Theme Customizer
 * Version: 1.0
 * Author: Logan Stellway
 * Author URI: www.loganstellway.com
 * License: GNU GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace LoganStellway\GoogleFonts;

// Prevent direct access to script
defined( 'ABSPATH' ) or die();

if ( ! class_exists('\LoganStellway\GoogleFonts\Registration') ) {
    class Registration
    {
        public function __construct() {
            if ( is_admin() ) {
                add_action( 'admin_menu', array( $this, 'adminMenu' ) );
                add_action( 'admin_init', array( $this, 'registerSettings' ) );
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'actionLinks' ) );
            }
        }

        /**
         * Initialize settings page
         */
        public function registerSettings()
        {
            register_setting( 'loganstellway-googlefonts', 'loganstellway-googlefonts-api-key' );

            add_settings_section(
                'loganstellway-googlefonts-api',
                __( 'API Options' ),
                function( $args ) {
                    echo '<p>Google API settings.</p>';
                },
                'loganstellway-googlefonts'
            );
         
            add_settings_field(
                'loganstellway-googlefonts-api-key',
                __( 'API Key' ),
                function( $args ) {
                    echo '<input type="password" name="loganstellway-googlefonts-api-key" value="' . get_option( 'loganstellway-googlefonts-api-key' ) . '">';
                },
                'loganstellway-googlefonts',
                'loganstellway-googlefonts-api'
            );
        }

        /**
         * Add admin menu
         */
        public function adminMenu()
        {
            add_submenu_page(
                'options-general.php',
                __( 'Google Fonts Settings' ),
                __( 'Google Fonts Settings' ),
                'manage_options',
                'loganstellway-googlefonts',
                array( $this, 'settings' )
            );
        }

        /**
         * Add action links
         * @param  array $links
         * @return array
         */
        public function actionLinks( $links )
        {
            return array_merge( $links, array(
                '<a href="' . admin_url( 'options-general.php?page=loganstellway-googlefonts' ) . '">Settings</a>',
            ) );
        }

        /**
         * Settings page
         */
        public function settings()
        {
            if ( ! current_user_can( 'manage_options' ) ) return;

            echo '<div class="wrap">' . 
                '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' . 
                '<form method="post" action="options.php">';
                    settings_fields( 'loganstellway-googlefonts' );
                    do_settings_sections( 'loganstellway-googlefonts' );
                    submit_button();
                echo '</form>' . 
            '</div>';
        }
    }

    new Registration();
}

/**
 * Register control
 */
define('GOOGLE_FONTS_CUSTOMIZE_PLUGIN_PATH', \plugin_dir_path( __FILE__ ));
define('GOOGLE_FONTS_CUSTOMIZE_PLUGIN_SCRIPT', \plugins_url( 'src/assets/scripts/control.js', __FILE__ ));

add_action('customize_register', function() {
    require_once GOOGLE_FONTS_CUSTOMIZE_PLUGIN_PATH . 'src/Customize/Control.php';
});

/**
 * Include Helpers
 */
require GOOGLE_FONTS_CUSTOMIZE_PLUGIN_PATH . 'src/Helpers.php';
