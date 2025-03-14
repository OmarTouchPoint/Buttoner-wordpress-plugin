<?php
if (!defined('ABSPATH')) {
    exit;
}

class Button_Manager_Shortcode {
    public function __construct() {
        add_shortcode('button_manager', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => '' // ID de la botonera
        ], $atts, 'button_manager');

        $button_sets = get_option('button_manager_sets', []);
        if (empty($atts['id']) || !isset($button_sets[$atts['id']])) {
            return '<p>' . esc_html__('No buttons available.', 'button-manager') . '</p>';
        }

        $buttons = $button_sets[$atts['id']];
        $output = '<div class="buttons-container">';
        $output .= '<div class="buttons-content">';
        foreach ($buttons as $button) {
            $output .= '<a href="' . esc_url($button['link']) . '" class="insta-button" target="_blank">';
            $output .= '<img src="' . esc_url($button['icon']) . '" width="40" height="40" alt="Icon" onerror="this.style.display=\'none\'"> ';
            $output .= '<span>' . esc_html($button['text']) . '</span></a>';
        }
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

    public function enqueue_frontend_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'button_manager')) {
            wp_enqueue_style('button-manager-frontend-styles', plugin_dir_url(__FILE__) . '../assets/css/styles.css');
        }
    }
}