<?php
/**
 * Plugin Name: Clarke Energy Calculator
 * Plugin URI: https://uzeda.com.br
 * Description: Calculadora de estratégia de economia de energia para empresas. Identifica a melhor solução energética baseada no perfil do usuário.
 * Version: 1.0.0
 * Author: Diogo Uzêda
 * Author URI: https://uzeda.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: clarke-energy-calculator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CLARKE_CALC_VERSION', '1.0.0');
define('CLARKE_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLARKE_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CLARKE_CALC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Clarke_Energy_Calculator {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-calculator-logic.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-calculator-ajax.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-lead-manager.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('clarke_calculator', array($this, 'render_calculator'));
        
        // Initialize AJAX handler
        new Clarke_Calculator_Ajax();
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'clarke_calculator')) {
            
            // Styles
            wp_enqueue_style(
                'clarke-calculator-style',
                CLARKE_CALC_PLUGIN_URL . 'assets/css/calculator.css',
                array(),
                CLARKE_CALC_VERSION
            );

            // Scripts
            wp_enqueue_script(
                'clarke-calculator-script',
                CLARKE_CALC_PLUGIN_URL . 'assets/js/calculator.js',
                array('jquery'),
                CLARKE_CALC_VERSION,
                true
            );

            // Localize script
            wp_localize_script('clarke-calculator-script', 'clarkeCalc', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('clarke_calculator_nonce'),
            ));
        }
    }

    /**
     * Render calculator shortcode
     */
    public function render_calculator($atts) {
        ob_start();
        include CLARKE_CALC_PLUGIN_DIR . 'templates/calculator-form.php';
        return ob_get_clean();
    }
}

/**
 * Plugin activation
 */
function clarke_calc_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'clarke_calculator_leads';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        company_type varchar(100) NOT NULL,
        company_size varchar(100) NOT NULL,
        monthly_expense varchar(100) NOT NULL,
        recommended_strategy varchar(100) NOT NULL,
        all_answers longtext NOT NULL,
        scores longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'clarke_calc_activate');

/**
 * Plugin deactivation
 */
function clarke_calc_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'clarke_calc_deactivate');

/**
 * Initialize plugin
 */
function clarke_calc_init() {
    Clarke_Energy_Calculator::get_instance();
}
add_action('plugins_loaded', 'clarke_calc_init');
