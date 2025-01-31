<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Image_Converter {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_filter('wp_generate_attachment_metadata', [$this, 'convert_image'], 10, 2);
        add_action('delete_attachment', [$this, 'delete_converted_image']);
        add_filter('wpic_skip_conversion', [$this, 'skip_conversion'], 10, 2);
    }

    public function add_plugin_page() {
        add_submenu_page(
            'tools.php',
            __('Conversor de Imágenes', 'conversor-imagenes-wp'),
            __('Conversor de Imágenes', 'conversor-imagenes-wp'),
            'manage_options',
            'conversor-imagenes-wp',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page() {
	    if (!current_user_can('manage_options')) {
	        return;
	    }
	
	    $webp_supported = function_exists('imagewebp');
	    $avif_supported = function_exists('imageavif');
	    $uploads_dir = wp_upload_dir();
	    $writable = wp_is_writable($uploads_dir['basedir']);
	    ?>
	    <div class="wrap wpic-admin">
	        <h1><?php _e('Conversor de Imágenes para WordPress', 'conversor-imagenes-wp'); ?></h1>
	        <?php if (!$writable): ?>
	            <div class="notice notice-error">
	                <p><?php _e('El directorio de uploads no tiene permisos de escritura. La conversión de imágenes no funcionará.', 'conversor-imagenes-wp'); ?></p>
	            </div>
	        <?php endif; ?>
	        <form method="post" action="options.php">
	            <?php settings_fields('wpic_settings'); ?>
	            <?php do_settings_sections('wpic_settings'); ?>
	            <table class="form-table">
	                <tr>
	                    <th scope="row"><?php _e('Habilitar conversión', 'conversor-imagenes-wp'); ?></th>
	                    <td>
	                        <input type="checkbox" name="wpic_enabled" id="wpic_enabled" value="1" <?php checked(1, get_option('wpic_enabled', 1)); ?> />
	                    </td>
	                </tr>
	                <tr>
	                    <th scope="row"><?php _e('Formato', 'conversor-imagenes-wp'); ?></th>
	                    <td>
	                        <select name="wpic_format" id="wpic_format" <?php echo !get_option('wpic_enabled', 1) ? 'disabled' : ''; ?>>
	                            <option value="webp" <?php selected(get_option('wpic_format'), 'webp'); ?>>WebP</option>
	                            <option value="avif" <?php selected(get_option('wpic_format'), 'avif'); ?>>AVIF</option>
	                        </select>
	                        <p id="format-support-message"></p>
	                    </td>
	                </tr>
	                <tr>
	                    <th scope="row"><?php _e('Calidad', 'conversor-imagenes-wp'); ?> (<span id="quality-value"><?php echo get_option('wpic_quality', 80); ?></span>%)</th>
	                    <td>
	                        <input type="range" id="quality-slider" name="wpic_quality" min="0" max="100" value="<?php echo get_option('wpic_quality', 80); ?>" <?php echo !get_option('wpic_enabled', 1) ? 'disabled' : ''; ?> />
	                    </td>
	                </tr>
	                <tr>
	                    <th scope="row"><?php _e('Tamaño máximo de imagen (MB)', 'conversor-imagenes-wp'); ?></th>
	                    <td>
	                        <input type="number" name="wpic_max_size" id="wpic_max_size" value="<?php echo get_option('wpic_max_size', 5); ?>" min="1" <?php echo !get_option('wpic_enabled', 1) ? 'disabled' : ''; ?> />
	                        <p class="description"><?php _e('No se convertirán imágenes más grandes que este tamaño.', 'conversor-imagenes-wp'); ?></p>
	                    </td>
	                </tr>
	                <tr>
	                    <th scope="row"><?php _e('Reemplazar URL por imagen convertida en el Front-End', 'conversor-imagenes-wp'); ?></th>
	                    <td>
	                        <input type="checkbox" name="wpic_replace_url" id="wpic_replace_url" value="1" <?php checked(1, get_option('wpic_replace_url', 0)); ?> <?php echo !get_option('wpic_enabled', 1) ? 'disabled' : ''; ?> />
	                    </td>
	                </tr>
	                <tr>
	                    <th scope="row"><?php _e('Añadir dimensiones automáticamente', 'conversor-imagenes-wp'); ?></th>
	                    <td>
	                        <input type="checkbox" name="wpic_add_dimensions" id="wpic_add_dimensions" value="1" <?php checked(1, get_option('wpic_add_dimensions', 0)); ?> <?php echo !get_option('wpic_enabled', 1) ? 'disabled' : ''; ?> />
	                        <p class="description"><?php _e('Añade los atributos width y height a las etiquetas <img> para mejorar el rendimiento.', 'conversor-imagenes-wp'); ?></p>
	                    </td>
	                </tr>
	            </table>
	            <?php submit_button(); ?>
	        </form>
	    </div>
	    <?php
	}

    public function convert_image($metadata, $attachment_id) {

	    if (!get_option('wpic_enabled', 1)) {
	        return $metadata;
	    }
	
	    $file_path = get_attached_file($attachment_id);
	    $file_size = filesize($file_path) / 1024 / 1024;
	    $max_size = get_option('wpic_max_size', 5);
	
	    if ($file_size > $max_size) {
	        error_log("Conversor de Imágenes: La imagen es demasiado grande para convertir ($file_size MB).");
	        return $metadata;
	    }
	
	    $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
	    $format = get_option('wpic_format', 'webp');
	    $quality = get_option('wpic_quality', 80);
	
	    if (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
	        return $metadata;
	    }
	
	    $new_file = preg_replace('/\.' . $file_ext . '$/', '.' . $format, $file_path);
	    $image = ($file_ext === 'png') ? imagecreatefrompng($file_path) : imagecreatefromjpeg($file_path);
	
	    if (!$image) {
	        error_log("Conversor de Imágenes: No se pudo cargar la imagen $file_path.");
	        return $metadata;
	    }
	
	    if ($format === 'webp' && function_exists('imagewebp')) {
	        imagewebp($image, $new_file, $quality);
	    } elseif ($format === 'avif' && function_exists('imageavif')) {
	        imageavif($image, $new_file, $quality);
	    }
	
	    imagedestroy($image);
	    return $metadata;
	}


    public function delete_converted_image($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $format = get_option('wpic_format', 'webp');

        if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
            $converted_file = preg_replace('/\.' . $file_ext . '$/', '.' . $format, $file_path);
            if (file_exists($converted_file)) {
                unlink($converted_file);
            }
        }
    }

    public function skip_conversion($skip, $attachment_id) {
        return $skip;
    }
}