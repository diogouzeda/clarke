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
        $this->maybe_upgrade_database();
    }

    /**
     * Check and upgrade database if needed
     */
    private function maybe_upgrade_database() {
        $db_version = get_option('clarke_calc_db_version', '1.0.0');
        
        // If version is less than 1.1.0, run upgrade
        if (version_compare($db_version, '1.1.0', '<')) {
            $this->upgrade_database();
            update_option('clarke_calc_db_version', '1.1.0');
        }
    }

    /**
     * Upgrade database schema
     */
    private function upgrade_database() {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'clarke_calculator_leads';
        
        // Check existing columns
        $existing_columns = $wpdb->get_col("DESCRIBE $leads_table", 0);
        
        if (!$existing_columns) {
            return; // Table doesn't exist yet
        }
        
        $new_columns = array(
            'lead_source' => "ALTER TABLE $leads_table ADD COLUMN lead_source text DEFAULT NULL AFTER hubspot_synced_at",
            'page_url' => "ALTER TABLE $leads_table ADD COLUMN page_url text DEFAULT NULL AFTER lead_source",
            'page_title' => "ALTER TABLE $leads_table ADD COLUMN page_title varchar(255) DEFAULT NULL AFTER page_url",
        );
        
        foreach ($new_columns as $column => $sql) {
            if (!in_array($column, $existing_columns)) {
                $wpdb->query($sql);
            }
        }
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-calculator-logic.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-calculator-ajax.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-lead-manager.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-logger.php';
        require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-hubspot-integration.php';
        
        // Admin only
        if (is_admin()) {
            require_once CLARKE_CALC_PLUGIN_DIR . 'includes/class-admin-settings.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('estrategia_energia', array($this, 'render_calculator'));
        
        // Initialize AJAX handler
        new Clarke_Calculator_Ajax();
        
        // Initialize Admin Settings
        if (is_admin()) {
            new Clarke_Admin_Settings();
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'estrategia_energia')) {
            
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
    
    $charset_collate = $wpdb->get_charset_collate();

    // Leads table
    $leads_table = $wpdb->prefix . 'clarke_calculator_leads';
    $sql_leads = "CREATE TABLE IF NOT EXISTS $leads_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        company_type varchar(100) NOT NULL,
        company_size varchar(100) NOT NULL,
        monthly_expense varchar(100) NOT NULL,
        recommended_strategy varchar(100) NOT NULL,
        all_answers longtext NOT NULL,
        scores longtext NOT NULL,
        hubspot_contact_id varchar(100) DEFAULT NULL,
        hubspot_synced_at datetime DEFAULT NULL,
        lead_source text DEFAULT NULL,
        page_url text DEFAULT NULL,
        page_title varchar(255) DEFAULT NULL,
        utm_source varchar(255) DEFAULT NULL,
        utm_medium varchar(255) DEFAULT NULL,
        utm_campaign varchar(255) DEFAULT NULL,
        utm_term varchar(255) DEFAULT NULL,
        utm_content varchar(255) DEFAULT NULL,
        referrer text DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        KEY email (email),
        KEY created_at (created_at)
    ) $charset_collate;";

    // Logs table
    $logs_table = $wpdb->prefix . 'clarke_calculator_logs';
    $sql_logs = "CREATE TABLE IF NOT EXISTS $logs_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        type varchar(50) NOT NULL,
        lead_id mediumint(9) DEFAULT NULL,
        message text NOT NULL,
        request_data longtext DEFAULT NULL,
        response_data longtext DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        referrer text DEFAULT NULL,
        utm_source varchar(255) DEFAULT NULL,
        utm_medium varchar(255) DEFAULT NULL,
        utm_campaign varchar(255) DEFAULT NULL,
        utm_term varchar(255) DEFAULT NULL,
        utm_content varchar(255) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        KEY type (type),
        KEY lead_id (lead_id),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_leads);
    dbDelta($sql_logs);
    
    // Update existing leads table if needed (add new columns)
    $existing_columns = $wpdb->get_col("DESCRIBE $leads_table", 0);
    
    $new_columns = array(
        'hubspot_contact_id' => "ALTER TABLE $leads_table ADD COLUMN hubspot_contact_id varchar(100) DEFAULT NULL AFTER scores",
        'hubspot_synced_at' => "ALTER TABLE $leads_table ADD COLUMN hubspot_synced_at datetime DEFAULT NULL AFTER hubspot_contact_id",
        'lead_source' => "ALTER TABLE $leads_table ADD COLUMN lead_source text DEFAULT NULL AFTER hubspot_synced_at",
        'page_url' => "ALTER TABLE $leads_table ADD COLUMN page_url text DEFAULT NULL AFTER lead_source",
        'page_title' => "ALTER TABLE $leads_table ADD COLUMN page_title varchar(255) DEFAULT NULL AFTER page_url",
        'utm_source' => "ALTER TABLE $leads_table ADD COLUMN utm_source varchar(255) DEFAULT NULL AFTER page_title",
        'utm_medium' => "ALTER TABLE $leads_table ADD COLUMN utm_medium varchar(255) DEFAULT NULL AFTER utm_source",
        'utm_campaign' => "ALTER TABLE $leads_table ADD COLUMN utm_campaign varchar(255) DEFAULT NULL AFTER utm_medium",
        'utm_term' => "ALTER TABLE $leads_table ADD COLUMN utm_term varchar(255) DEFAULT NULL AFTER utm_campaign",
        'utm_content' => "ALTER TABLE $leads_table ADD COLUMN utm_content varchar(255) DEFAULT NULL AFTER utm_term",
        'referrer' => "ALTER TABLE $leads_table ADD COLUMN referrer text DEFAULT NULL AFTER utm_content",
        'ip_address' => "ALTER TABLE $leads_table ADD COLUMN ip_address varchar(45) DEFAULT NULL AFTER referrer",
        'user_agent' => "ALTER TABLE $leads_table ADD COLUMN user_agent text DEFAULT NULL AFTER ip_address",
    );
    
    foreach ($new_columns as $column => $sql) {
        if (!in_array($column, $existing_columns)) {
            $wpdb->query($sql);
        }
    }
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
