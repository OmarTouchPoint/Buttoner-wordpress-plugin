<?php
/**
 * Plugin Name: Button Manager
 * Plugin URI: https://tusitio.com
 * Description: Permite gestionar múltiples botoneras desde el panel de administración de WordPress.
 * Version: 1.4
 * Author: Omar
 * Author URI: https://tusitio.com
 * License: GPL2
 * Text Domain: button-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos necesarios
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-button-manager-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/class-button-manager-shortcode.php';

// Inicializar el plugin
function button_manager_init() {
    // Inicializar la administración
    if (is_admin()) {
        new Button_Manager_Admin();
    }

    // Inicializar shortcodes
    new Button_Manager_Shortcode();
}
add_action('plugins_loaded', 'button_manager_init');
