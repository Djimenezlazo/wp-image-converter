<?php
function wpic_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Conversor de Imágenes</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wp_image_converter'); ?>
            <table class="form-table">
                <tr>
                    <th>Activar Conversión</th>
                    <td><input type="checkbox" name="wpic_enabled" value="1" <?php checked(get_option('wpic_enabled', 1)); ?>></td>
                </tr>
                <tr>
                    <th>Formato de Conversión</th>
                    <td>
                        <select name="wpic_format">
                            <option value="webp" <?php selected(get_option('wpic_format', 'webp'), 'webp'); ?>>WebP</option>
                            <option value="avif" <?php selected(get_option('wpic_format', 'webp'), 'avif'); ?>>AVIF</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Calidad</th>
                    <td>
                        <input type="range" name="wpic_quality" min="10" max="100" value="<?php echo esc_attr(get_option('wpic_quality', 80)); ?>">
                        <span><?php echo esc_attr(get_option('wpic_quality', 80)); ?>%</span>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
