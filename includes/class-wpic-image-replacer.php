<?php
if (!defined('ABSPATH')) {
    exit;
}

class WPIC_Image_Replacer {
    public function __construct() {

        if (get_option('wpic_enabled', 1) && get_option('wpic_replace_url', 0)) {
            ob_start([$this, 'replace_images_in_html']);
        }
    }

    public function replace_images_in_html($html) {
        $format = get_option('wpic_format', 'webp');
        $add_dimensions = get_option('wpic_add_dimensions', 0);

        return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)\.(jpg|jpeg|png|webp|avif)["\'][^>]*>/i', function ($matches) use ($format, $add_dimensions) {
            $original_src = $matches[1] . '.' . $matches[2];
            $converted_src = $matches[1] . '.' . $format;


            if (file_exists(str_replace(get_site_url(), ABSPATH, $converted_src))) {
                $img_tag = str_replace($original_src, $converted_src, $matches[0]);


                if ($add_dimensions) {
                    $image_path = str_replace(get_site_url(), ABSPATH, $converted_src);
                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);
                        if ($width && $height) {
                            $img_tag = preg_replace('/<img/', '<img width="' . $width . '" height="' . $height . '"', $img_tag);
                        }
                    }
                }

                return $img_tag;
            }

            return $matches[0];
        }, $html);
    }
}