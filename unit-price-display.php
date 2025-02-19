<?php
/**
 * Plugin Name: Unit Price Display for WooCommerce
 * Plugin URI: #
 * Description: Adds unit of measurement information after product price
 * Version: 1.0.0
 * Author: WebRainbow
 * Author URI: #
 * Text Domain: unit-price-display
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Unit_Price_Display')) {
    class Unit_Price_Display {
        public function __construct() {
            add_action('plugins_loaded', array($this, 'init'));
        }

        public function init() {
            // Check if WooCommerce is active
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
                return;
            }

            // Add field to product settings
            add_action('woocommerce_product_options_general_product_data', array($this, 'add_unit_measure_field'));
            
            // Save field value
            add_action('woocommerce_process_product_meta', array($this, 'save_unit_measure_field'));
            
            // Display information after price
            add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_unit_measure'), 15);
            add_action('woocommerce_get_price_html', array($this, 'append_unit_to_price'), 100, 2);
            
            // Enqueue styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
            
            // Load translations
            load_plugin_textdomain('unit-price-display', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function woocommerce_missing_notice() {
            ?>
            <div class="error">
                <p><?php _e('Unit Price Display requires WooCommerce to be installed and active.', 'unit-price-display'); ?></p>
            </div>
            <?php
        }

        public function add_unit_measure_field() {
            woocommerce_wp_select(array(
                'id' => '_unit_measure',
                'label' => __('Unit of Measurement', 'unit-price-display'),
                'desc_tip' => true,
                'description' => __('Select the unit of measurement to display after the price', 'unit-price-display'),
                'options' => array(
                    '' => __('Select...', 'unit-price-display'),
                    'piece' => __('per piece', 'unit-price-display'),
                    'kilogram' => __('per kilogram', 'unit-price-display'),
                    '100gram' => __('per 100 grams', 'unit-price-display'),
                    '200gram' => __('per 200 grams', 'unit-price-display'),
                    '300gram' => __('per 300 grams', 'unit-price-display'),
                    '400gram' => __('per 400 grams', 'unit-price-display'),
                    '500gram' => __('per 500 grams', 'unit-price-display'),
                    '600gram' => __('per 600 grams', 'unit-price-display'),
                    '700gram' => __('per 700 grams', 'unit-price-display'),
                    '800gram' => __('per 800 grams', 'unit-price-display'),
                    '900gram' => __('per 900 grams', 'unit-price-display'),
                    'gram' => __('per gram', 'unit-price-display'),
                    'meter' => __('per meter', 'unit-price-display'),
                    'liter' => __('per liter', 'unit-price-display'),
                    'pair' => __('per pair', 'unit-price-display'),
                    'pack' => __('per pack', 'unit-price-display'),
                    'set' => __('per set', 'unit-price-display')
                )
            ));
        }

        public function save_unit_measure_field($post_id) {
            $unit_measure = isset($_POST['_unit_measure']) ? sanitize_text_field($_POST['_unit_measure']) : '';
            update_post_meta($post_id, '_unit_measure', $unit_measure);
        }

        public function enqueue_styles() {
            wp_enqueue_style(
                'unit-price-display',
                plugins_url('assets/css/style.css', __FILE__),
                array(),
                '1.0.0'
            );
        }

        public function append_unit_to_price($price_html, $product) {
            if (!$product || !is_object($product)) {
                return $price_html;
            }

            $unit_measure = get_post_meta($product->get_id(), '_unit_measure', true);
            
            if (!empty($unit_measure)) {
                return $price_html . '<div class="unit-measure-info">' . 
                       esc_html(sprintf(__('Price is %s', 'unit-price-display'), $unit_measure)) . 
                       '</div>';
            }
            
            return $price_html;
        }

        public function display_unit_measure() {
            global $product;
            
            if (!$product || !is_object($product)) {
                return;
            }

            $unit_measure = get_post_meta($product->get_id(), '_unit_measure', true);
            
            if (!empty($unit_measure) && !is_product()) {
                echo '<div class="unit-measure-info">';
                echo esc_html(sprintf(__('Price is %s', 'unit-price-display'), $unit_measure));
                echo '</div>';
            }
        }
    }

    new Unit_Price_Display();
} 