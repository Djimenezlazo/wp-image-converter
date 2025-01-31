<?php
if (!defined('ABSPATH')) {
    exit;
}

class WPIC_Settings {
    public static function register_settings() {
        register_setting('wpic_settings', 'wpic_enabled', ['sanitize_callback' => 'absint']);
        register_setting('wpic_settings', 'wpic_format', ['sanitize_callback' => [__CLASS__, 'sanitize_format']]);
        register_setting('wpic_settings', 'wpic_quality', ['sanitize_callback' => [__CLASS__, 'sanitize_quality']]);
        register_setting('wpic_settings', 'wpic_replace_url', ['sanitize_callback' => 'absint']);
        register_setting('wpic_settings', 'wpic_max_size', ['sanitize_callback' => 'absint']);
        register_setting('wpic_settings', 'wpic_add_dimensions', ['sanitize_callback' => 'absint']); // Nuevo campo
    }

    public static function sanitize_format($format) {
        return in_array($format, ['webp', 'avif']) ? $format : 'webp';
    }

    public static function sanitize_quality($quality) {
        return max(0, min(100, (int) $quality));
    }
}
add_action('admin_init', ['WPIC_Settings', 'register_settings']);