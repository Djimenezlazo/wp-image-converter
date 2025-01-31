<?php
/**
 * Plugin Name: Conversor de Imágenes para WordPress
 * Description: Convierte imágenes subidas a WebP o AVIF según la configuración del usuario.
 * Version: 1.2
 * Author: David Jiménez Lazo
 * Author URI: https://github.com/Djimenezlazo/
 * Text Domain: conversor-imagenes-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos necesarios
require_once plugin_dir_path(__FILE__) . 'includes/class-wp-image-converter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wpic-image-replacer.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';


function wpic_init() {
    new WP_Image_Converter();
    if (!is_admin()) {
        new WPIC_Image_Replacer();
    }
}
add_action('plugins_loaded', 'wpic_init');


function wpic_enqueue_admin_assets($hook) {
    if ($hook === 'tools_page_conversor-imagenes-wp') {
        wp_enqueue_style('wpic-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css');
        wp_enqueue_script('wpic-admin-scripts', plugin_dir_url(__FILE__) . 'assets/js/admin-scripts.js', ['jquery'], null, true);


        wp_localize_script('wpic-admin-scripts', 'wpic_vars', [
            'webp_supported' => function_exists('imagewebp'),
            'avif_supported' => function_exists('imageavif'),
            'webp_not_supported' => __('Tu servidor no tiene los requisitos para usar la conversión a WebP. Asegúrate de tener instalada la extensión GD con soporte para WebP.', 'conversor-imagenes-wp'),
            'avif_not_supported' => __('Tu servidor no tiene los requisitos para usar la conversión a AVIF. Asegúrate de tener instalada la extensión GD con soporte para AVIF.', 'conversor-imagenes-wp'),
            'webp_supported_message' => __('WebP está soportado en tu servidor.', 'conversor-imagenes-wp'),
            'avif_supported_message' => __('AVIF está soportado en tu servidor.', 'conversor-imagenes-wp'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'wpic_enqueue_admin_assets');