<?php
/**
 *  Plugin Name: getData YEXT API
 *  Description: Abfrage und Verarbeitung Standort Kontaktdaten und Öffnungszeiten via YEXT
 *  Text Domain: blyxt
 *  Version: 1.0
 *  Author: Faisal Rahim Webdesign
 *  Author URI: mailto:hello@faisman.de
 *  Min WP Version: 3.0
 */


include ('libs/functions.php');
require ('libs/config.php');
require_once ('libs/frwd_yext.class.php');

define('__fryext__pluginclearName__', 'BL Yext-Data');
define('__fryext__pluginName__', 'getData_yxt');
define('__fryext__pluginFolder__',__fryext__pluginName__.DIRECTORY_SEPARATOR);
define('__fryext__pluginURL__',plugins_url(__fryext__pluginName__.'/'));
define('__fryext__pluginPATH__', plugin_dir_path( __DIR__ ));
define('__fryext__dbtable__', 'blyxt');
define('__bl_locationURL__', 'https://domain.tld/location-finder/');
define('__devMode__', false);

if(__devMode__){
    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);
}

/**
 * install plugin db-table
 */

function frwd_yext_install() {
    global $wpdb;
    $blyxtDB = $wpdb->prefix . __fryext__dbtable__;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql_frwd_yext = "
        CREATE TABLE IF NOT EXISTS `".$blyxtDB."` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
          `livemode` tinyint(1) NOT NULL,
          `sandbox_apiKey` varchar(255) NOT NULL,
          `sandbox_accountID` varchar(255) NOT NULL,
          `live_apiKey` varchar(255) NOT NULL,
          `live_accountID` varchar(255) NOT NULL,
          `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

    ";
    $sql_initdata = "
    INSERT INTO $blyxtDB (livemode, sandbox_apiKey, sandbox_accountID,live_apiKey,live_accountID) VALUES (0,'','','','')
    ";
    // install table
    dbDelta($sql_frwd_yext);
    dbDelta($sql_initdata);
}
register_activation_hook(__FILE__,'frwd_yext_install');

function frwd_yext_uninstall(){
    global $wpdb;
    $blyxtDB = $wpdb->prefix . __fryext__dbtable__;
    $sql = "DROP TABLE IF EXISTS $blyxtDB";
    $wpdb->query($sql);
}

register_uninstall_hook(__FILE__, 'frwd_yext_uninstall');


add_action( 'wp_enqueue_scripts', 'fryxt_frontend_queue' );
function fryxt_frontend_queue() {
    wp_enqueue_style ( __fryext__pluginName__.'-style', plugins_url('assets/css/style.css', __FILE__) );
}

add_action('admin_init', 'fryxt_BE_css');
function fryxt_BE_css() {
    if(is_plugin_page()) {
        wp_enqueue_style(__fryext__pluginName__.'css', plugins_url( '/assets/css/fryext.backend.css', __FILE__));
        wp_enqueue_style(__fryext__pluginName__.'bootstrap-css', plugins_url( '/assets/css/bootstrap.min.css', __FILE__));
        wp_enqueue_script( __fryext__pluginName__.'js', plugins_url('/assets/js/main.js', __FILE__), array( 'jquery' ), '20212-12-20', true);
    }
}

/**
 * Register a custom menu page.
 */
function fryext_adminmenu(){

    add_menu_page(
        __fryext__pluginclearName__,
        __fryext__pluginclearName__,
        'edit_pages',
        'blyxtdata',
        'frwd_menu_page',
        'dashicons-location-alt'
    );
    add_submenu_page(
        'blyxtdata',
        'Einstellungen',
        'Einstellungen',
        'edit_pages',
        'blyxtdata-settings',
        'frwd_submenu_page'
    );
}
add_action( 'admin_menu', 'fryext_adminmenu' );

function frwd_submenu_page(){
    require('libs/frwd_yext.settings.php');
}

function frwd_menu_page(){
    require('libs/frwd_yext.overview.php');
}


function bl_locationdata_render($atts) {
    global $post;
    $frYext = new frwdYext();
    $a = shortcode_atts( array(
        'location' => $post->post_name,
    ), $atts );

    return $frYext->renderQuickinfo($a['location']);

}
add_shortcode( 'bl_location', 'bl_locationdata_render' );


add_action( 'init', 'bl_load_textdomain' );
function bl_load_textdomain() {
    load_plugin_textdomain( 'blyxt', false, dirname( plugin_basename( __FILE__ )) . '/languages' );
}