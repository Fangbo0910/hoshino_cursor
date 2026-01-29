<?php
/**
 * Plugin Name: Hoshino Cursor
 * Description: Custom cursor with SVG and particle effects.
 * Version: 1.0.0
 * Author: Hoshino
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HOSHINO_CURSOR_VERSION', '1.0.0');
define('HOSHINO_CURSOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HOSHINO_CURSOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// 默认配置，用于首次安装与清洗时的兜底。
function hoshino_cursor_get_defaults() {
    return array(
        'main_svg_id' => 0,
        'main_svg_inline' => '',
        'input_svg_id' => 0,
        'input_svg_inline' => '',
        'main_enabled' => 1,
        'input_enabled' => 1,
        'particle_count' => 10,
        'particle_color' => '#ff5fa2',
        'particle_speed' => 420,
    );
}

// 读取保存的配置并与默认值合并，补齐缺失项。
function hoshino_cursor_get_options() {
    $defaults = hoshino_cursor_get_defaults();
    $options = get_option('hoshino_cursor_options', array());
    return wp_parse_args($options, $defaults);
}

// 允许在媒体库中上传 SVG 光标资源。
add_filter('upload_mimes', 'hoshino_cursor_allow_svg');
function hoshino_cursor_allow_svg($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}

// 注册主设置页与隐藏的欢迎页。
add_action('admin_menu', 'hoshino_cursor_admin_menu');
function hoshino_cursor_admin_menu() {
    add_menu_page(
        'Hoshino光标插件设置',
        'Hoshino光标插件设置',
        'manage_options',
        'hoshino-cursor',
        'hoshino_cursor_settings_page',
        'dashicons-admin-customizer'
    );

    add_submenu_page(
        null,
        'Hoshino光标插件设置',
        'Hoshino光标插件设置',
        'manage_options',
        'hoshino-cursor-welcome',
        'hoshino_cursor_welcome_page'
    );
}

// 使用 Settings API 注册设置项与后台字段。
add_action('admin_init', 'hoshino_cursor_register_settings');
function hoshino_cursor_register_settings() {
    register_setting('hoshino_cursor_settings', 'hoshino_cursor_options', 'hoshino_cursor_sanitize_options');

    add_settings_section('hoshino_cursor_main_section', '光标设置', '__return_false', 'hoshino-cursor');

    add_settings_field('main_svg_id', '常规态SVG上传', 'hoshino_cursor_render_media_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'main_svg_id',
        'label' => '常规态SVG',
    ));

    add_settings_field('main_svg_inline', '常规态SVG粘贴', 'hoshino_cursor_render_inline_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'main_svg_inline',
        'placeholder' => '<svg ...></svg>',
    ));

    add_settings_field('input_svg_id', '输入态SVG上传', 'hoshino_cursor_render_media_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'input_svg_id',
        'label' => '输入态SVG',
    ));

    add_settings_field('input_svg_inline', '输入态SVG粘贴', 'hoshino_cursor_render_inline_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'input_svg_inline',
        'placeholder' => '<svg ...></svg>',
    ));

    add_settings_field('main_enabled', '启用常规态光标', 'hoshino_cursor_render_checkbox_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'main_enabled',
    ));

    add_settings_field('input_enabled', '启用输入态光标', 'hoshino_cursor_render_checkbox_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'input_enabled',
    ));

    add_settings_field('particle_count', '粒子数量', 'hoshino_cursor_render_number_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'particle_count',
        'min' => 4,
        'max' => 20,
        'step' => 1,
    ));

    add_settings_field('particle_color', '粒子颜色', 'hoshino_cursor_render_color_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'particle_color',
    ));

    add_settings_field('particle_speed', '粒子速度(毫秒)', 'hoshino_cursor_render_number_field', 'hoshino-cursor', 'hoshino_cursor_main_section', array(
        'id' => 'particle_speed',
        'min' => 200,
        'max' => 800,
        'step' => 10,
    ));
}

// 渲染 SVG 上传字段，包含预览与清除按钮。
function hoshino_cursor_render_media_field($args) {
    $options = hoshino_cursor_get_options();
    $id = $args['id'];
    $value = isset($options[$id]) ? (int) $options[$id] : 0;
    $url = $value ? wp_get_attachment_url($value) : '';
    $input_id = esc_attr($id);
    $url_id = esc_attr($id . '_url');

    echo '<div class="hoshino-media-field">';
    echo '<input type="hidden" id="' . $input_id . '" name="hoshino_cursor_options[' . $input_id . ']" value="' . esc_attr($value) . '" />';
    echo '<input type="text" id="' . $url_id . '" value="' . esc_url($url) . '" class="regular-text" readonly />';
    echo '<button type="button" class="button hoshino-media-upload" data-target="' . $input_id . '" data-preview="' . $url_id . '">选择SVG</button> ';
    echo '<button type="button" class="button hoshino-media-clear" data-target="' . $input_id . '" data-preview="' . $url_id . '">清除</button>';
    echo '</div>';
}

// 渲染 SVG 粘贴文本框。
function hoshino_cursor_render_inline_field($args) {
    $options = hoshino_cursor_get_options();
    $id = $args['id'];
    $value = isset($options[$id]) ? $options[$id] : '';
    $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

    echo '<textarea class="large-text code" rows="6" name="hoshino_cursor_options[' . esc_attr($id) . ']" placeholder="' . esc_attr($placeholder) . '">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">粘贴内容优先级高于上传。</p>';
}

// 渲染启用/禁用光标状态的复选框。
function hoshino_cursor_render_checkbox_field($args) {
    $options = hoshino_cursor_get_options();
    $id = $args['id'];
    $checked = !empty($options[$id]);
    echo '<label><input type="checkbox" name="hoshino_cursor_options[' . esc_attr($id) . ']" value="1" ' . checked(true, $checked, false) . ' /> 启用</label>';
}

// 渲染带范围限制的数字输入框。
function hoshino_cursor_render_number_field($args) {
    $options = hoshino_cursor_get_options();
    $id = $args['id'];
    $value = isset($options[$id]) ? (int) $options[$id] : 0;
    $min = isset($args['min']) ? (int) $args['min'] : 0;
    $max = isset($args['max']) ? (int) $args['max'] : 0;
    $step = isset($args['step']) ? (int) $args['step'] : 1;

    echo '<input type="number" name="hoshino_cursor_options[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" step="' . esc_attr($step) . '" />';
}

// 渲染粒子颜色选择器。
function hoshino_cursor_render_color_field($args) {
    $options = hoshino_cursor_get_options();
    $id = $args['id'];
    $value = isset($options[$id]) ? $options[$id] : '#ff5fa2';

    echo '<input type="text" class="hoshino-color-field" name="hoshino_cursor_options[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" data-default-color="#ff5fa2" />';
}

// 渲染插件主设置页面。
function hoshino_cursor_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Hoshino光标插件设置</h1>';
    echo '<p>上传或粘贴SVG来自定义光标。粘贴内容将优先使用。</p>';
    echo '<form method="post" action="options.php">';
    settings_fields('hoshino_cursor_settings');
    do_settings_sections('hoshino-cursor');
    submit_button();
    echo '</form>';
    echo '</div>';
}

// 首次进入设置页时跳转到欢迎页。
add_action('admin_init', 'hoshino_cursor_maybe_redirect_welcome');
function hoshino_cursor_maybe_redirect_welcome() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'hoshino-cursor') {
        $done = get_option('hoshino_cursor_welcome_done');
        if (!$done) {
            wp_safe_redirect(admin_url('admin.php?page=hoshino-cursor-welcome'));
            exit;
        }
    }
}

// 处理欢迎页确认并写入已完成标记。
add_action('admin_post_hoshino_cursor_welcome_done', 'hoshino_cursor_handle_welcome_done');
function hoshino_cursor_handle_welcome_done() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    check_admin_referer('hoshino_cursor_welcome');
    update_option('hoshino_cursor_welcome_done', 1);
    wp_safe_redirect(admin_url('admin.php?page=hoshino-cursor'));
    exit;
}

// 渲染只出现一次的欢迎页及确认按钮。
function hoshino_cursor_welcome_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>欢迎使用 Hoshino 光标插件</h1>';
    echo '<p>这是由Fangbo构建的WordPress插件，感谢你的使用，希望你喜欢。</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('hoshino_cursor_welcome');
    echo '<input type="hidden" name="action" value="hoshino_cursor_welcome_done" />';
    echo '<p style="margin-top: 36px;">';
    echo '<button type="submit" class="button button-primary" id="hoshino-welcome-ok">OK</button>';
    echo '</p>';
    echo '</form>';
    echo '</div>';
    echo '<script type="text/javascript">';
    echo 'document.addEventListener("DOMContentLoaded", function () {';
    echo '  var btn = document.getElementById("hoshino-welcome-ok");';
    echo '  if (!btn) { return; }';
    echo '  btn.addEventListener("click", function () {';
    echo '    window.open("https://blog.dengfangbo.com", "_blank", "noopener");';
    echo '  });';
    echo '});';
    echo '</script>';
}

// 仅在插件设置页加载后台脚本与样式。
add_action('admin_enqueue_scripts', 'hoshino_cursor_admin_assets');
function hoshino_cursor_admin_assets($hook) {
    if ($hook !== 'toplevel_page_hoshino-cursor') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('hoshino-cursor-admin', HOSHINO_CURSOR_PLUGIN_URL . 'assets/admin.js', array('jquery', 'wp-color-picker'), HOSHINO_CURSOR_VERSION, true);
}

// 清洗并规范化所有保存的设置。
function hoshino_cursor_sanitize_options($input) {
    $defaults = hoshino_cursor_get_defaults();
    $output = array();

    $output['main_svg_id'] = isset($input['main_svg_id']) ? absint($input['main_svg_id']) : 0;
    $output['input_svg_id'] = isset($input['input_svg_id']) ? absint($input['input_svg_id']) : 0;

    $output['main_svg_inline'] = isset($input['main_svg_inline']) ? hoshino_cursor_sanitize_svg($input['main_svg_inline']) : '';
    $output['input_svg_inline'] = isset($input['input_svg_inline']) ? hoshino_cursor_sanitize_svg($input['input_svg_inline']) : '';

    $output['main_enabled'] = !empty($input['main_enabled']) ? 1 : 0;
    $output['input_enabled'] = !empty($input['input_enabled']) ? 1 : 0;

    $count = isset($input['particle_count']) ? (int) $input['particle_count'] : $defaults['particle_count'];
    $output['particle_count'] = min(20, max(4, $count));

    $color = isset($input['particle_color']) ? sanitize_hex_color($input['particle_color']) : $defaults['particle_color'];
    $output['particle_color'] = $color ? $color : $defaults['particle_color'];

    $speed = isset($input['particle_speed']) ? (int) $input['particle_speed'] : $defaults['particle_speed'];
    $output['particle_speed'] = min(800, max(200, $speed));

    return $output;
}

// 使用严格白名单清洗 SVG 内容。
function hoshino_cursor_sanitize_svg($svg) {
    if (empty($svg)) {
        return '';
    }

    $allowed_tags = array(
        'svg' => array(
            'xmlns' => true,
            'width' => true,
            'height' => true,
            'viewBox' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'g' => array(
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'path' => array(
            'd' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'rect' => array(
            'x' => true,
            'y' => true,
            'width' => true,
            'height' => true,
            'rx' => true,
            'ry' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'line' => array(
            'x1' => true,
            'y1' => true,
            'x2' => true,
            'y2' => true,
            'stroke' => true,
            'stroke-width' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'polyline' => array(
            'points' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'polygon' => array(
            'points' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'opacity' => true,
            'transform' => true,
            'id' => true,
        ),
        'defs' => array(),
        'linearGradient' => array(
            'id' => true,
            'x1' => true,
            'y1' => true,
            'x2' => true,
            'y2' => true,
            'gradientUnits' => true,
            'gradientTransform' => true,
        ),
        'radialGradient' => array(
            'id' => true,
            'cx' => true,
            'cy' => true,
            'r' => true,
            'fx' => true,
            'fy' => true,
            'gradientUnits' => true,
            'gradientTransform' => true,
        ),
        'stop' => array(
            'offset' => true,
            'stop-color' => true,
            'stop-opacity' => true,
        ),
        'clipPath' => array(
            'id' => true,
        ),
        'mask' => array(
            'id' => true,
        ),
    );

    return wp_kses($svg, $allowed_tags);
}

// 解析 SVG 尺寸并按 1/2 缩放到不超过 40px。
function hoshino_cursor_extract_size($svg) {
    $size = 0;
    $width = null;
    $height = null;

    if (preg_match('/\bwidth=["\']?([0-9.]+)(px)?["\']?/i', $svg, $match)) {
        $width = (float) $match[1];
    }

    if (preg_match('/\bheight=["\']?([0-9.]+)(px)?["\']?/i', $svg, $match)) {
        $height = (float) $match[1];
    }

    if ($width && $height) {
        $size = max($width, $height);
    } elseif (preg_match('/\bviewBox=["\']?([0-9.\s\-]+)["\']?/i', $svg, $match)) {
        $parts = preg_split('/\s+/', trim($match[1]));
        if (count($parts) >= 4) {
            $view_width = abs((float) $parts[2]);
            $view_height = abs((float) $parts[3]);
            if ($view_width > 0 && $view_height > 0) {
                $size = max($view_width, $view_height);
            }
        }
    }

    if ($size <= 0) {
        $size = 32;
    }

    while ($size > 40) {
        $size *= 0.5;
    }

    return $size;
}

// 从粘贴内容或附件 ID 获取 SVG，并解析尺寸。
function hoshino_cursor_resolve_svg($inline_svg, $attachment_id) {
    $svg = '';

    if (!empty($inline_svg)) {
        $svg = hoshino_cursor_sanitize_svg($inline_svg);
    } elseif (!empty($attachment_id)) {
        $path = get_attached_file($attachment_id);
        if ($path && file_exists($path)) {
            $content = file_get_contents($path);
            if ($content) {
                $svg = hoshino_cursor_sanitize_svg($content);
            }
        }
    }

    $size = $svg ? hoshino_cursor_extract_size($svg) : 32;
    return array($svg, $size);
}

// 加载前端资源并将设置传入 JS。
add_action('wp_enqueue_scripts', 'hoshino_cursor_enqueue_assets');
function hoshino_cursor_enqueue_assets() {
    $options = hoshino_cursor_get_options();

    if (empty($options['main_enabled']) && empty($options['input_enabled'])) {
        return;
    }

    wp_enqueue_style('hoshino-cursor', HOSHINO_CURSOR_PLUGIN_URL . 'assets/cursor.css', array(), HOSHINO_CURSOR_VERSION);
    wp_enqueue_script('hoshino-cursor', HOSHINO_CURSOR_PLUGIN_URL . 'assets/cursor.js', array(), HOSHINO_CURSOR_VERSION, true);

    list($main_svg, $main_size) = hoshino_cursor_resolve_svg($options['main_svg_inline'], $options['main_svg_id']);
    list($input_svg, $input_size) = hoshino_cursor_resolve_svg($options['input_svg_inline'], $options['input_svg_id']);

    $size_candidates = array();
    if (!empty($options['main_enabled']) && !empty($main_svg)) {
        $size_candidates[] = $main_size;
    }
    if (!empty($options['input_enabled']) && !empty($input_svg)) {
        $size_candidates[] = $input_size;
    }

    $cursor_size = !empty($size_candidates) ? max($size_candidates) : 32;

    wp_localize_script('hoshino-cursor', 'HoshinoCursorSettings', array(
        'mainEnabled' => !empty($options['main_enabled']),
        'inputEnabled' => !empty($options['input_enabled']),
        'mainSvg' => $main_svg,
        'inputSvg' => $input_svg,
        'cursorSize' => $cursor_size,
        'particleCount' => (int) $options['particle_count'],
        'particleColor' => $options['particle_color'],
        'particleSpeed' => (int) $options['particle_speed'],
    ));
}
