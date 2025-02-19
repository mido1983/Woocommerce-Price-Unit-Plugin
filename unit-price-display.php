<?php
/**
 * Plugin Name: Unit Price Display for WooCommerce
 * Description: Добавляет информацию о единице измерения после короткого описания продукта
 * Version: 1.0.0
 * Author:WebRainbow
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
            // Проверяем, активирован ли WooCommerce
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
                return;
            }

            // Добавляем поле в настройки продукта
            add_action('woocommerce_product_options_general_product_data', array($this, 'add_unit_measure_field'));
            
            // Сохраняем значение поля
            add_action('woocommerce_process_product_meta', array($this, 'save_unit_measure_field'));
            
            // Выводим информацию после короткого описания
            add_action('woocommerce_after_shop_loop_item', array($this, 'display_unit_measure'), 15);
            add_action('woocommerce_single_product_summary', array($this, 'display_unit_measure'), 25);
            
            // Подключаем стили
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
            
            // Загружаем переводы
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
                'label' => __('Единица измерения', 'unit-price-display'),
                'desc_tip' => true,
                'description' => __('Выберите единицу измерения для отображения после цены', 'unit-price-display'),
                'options' => array(
                    '' => __('Выберите...', 'unit-price-display'),
                    'штуку' => __('за штуку', 'unit-price-display'),
                    'килограмм' => __('за килограмм', 'unit-price-display'),
                    'грамм' => __('за грамм', 'unit-price-display'),
                    'метр' => __('за метр', 'unit-price-display'),
                    'литр' => __('за литр', 'unit-price-display'),
                    'пару' => __('за пару', 'unit-price-display'),
                    'упаковку' => __('за упаковку', 'unit-price-display'),
                    'набор' => __('за набор', 'unit-price-display')
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

        public function display_unit_measure() {
            global $product;
            
            if (!$product || !is_object($product)) {
                return;
            }

            $unit_measure = get_post_meta($product->get_id(), '_unit_measure', true);
            
            if (!empty($unit_measure)) {
                echo '<div class="unit-measure-info">';
                echo esc_html(sprintf(__('Цена указана %s', 'unit-price-display'), $unit_measure));
                echo '</div>';
            }
        }
    }

    new Unit_Price_Display();
} 