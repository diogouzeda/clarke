<?php
/**
 * Logger Class
 * Handles logging of all plugin activities
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_Logger {

    /**
     * Log types
     */
    const TYPE_FORM_SUBMIT = 'form_submit';
    const TYPE_LEAD_CREATED = 'lead_created';
    const TYPE_HUBSPOT_SYNC = 'hubspot_sync';
    const TYPE_HUBSPOT_ERROR = 'hubspot_error';
    const TYPE_HUBSPOT_RETRY = 'hubspot_retry';
    const TYPE_SYSTEM = 'system';

    /**
     * Get table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'clarke_calculator_logs';
    }

    /**
     * Create log table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            lead_id mediumint(9) DEFAULT NULL,
            message text NOT NULL,
            request_data longtext DEFAULT NULL,
            response_data longtext DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            utm_source varchar(255) DEFAULT NULL,
            utm_medium varchar(255) DEFAULT NULL,
            utm_campaign varchar(255) DEFAULT NULL,
            utm_content varchar(255) DEFAULT NULL,
            utm_term varchar(255) DEFAULT NULL,
            referrer text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY lead_id (lead_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Add a log entry
     */
    public static function log($type, $message, $data = array()) {
        global $wpdb;

        $table_name = self::get_table_name();

        $insert_data = array(
            'type' => sanitize_key($type),
            'message' => sanitize_text_field($message),
            'lead_id' => isset($data['lead_id']) ? intval($data['lead_id']) : null,
            'request_data' => isset($data['request']) ? wp_json_encode($data['request']) : null,
            'response_data' => isset($data['response']) ? wp_json_encode($data['response']) : null,
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null,
            'utm_source' => isset($data['utm_source']) ? sanitize_text_field($data['utm_source']) : null,
            'utm_medium' => isset($data['utm_medium']) ? sanitize_text_field($data['utm_medium']) : null,
            'utm_campaign' => isset($data['utm_campaign']) ? sanitize_text_field($data['utm_campaign']) : null,
            'utm_content' => isset($data['utm_content']) ? sanitize_text_field($data['utm_content']) : null,
            'utm_term' => isset($data['utm_term']) ? sanitize_text_field($data['utm_term']) : null,
            'referrer' => isset($data['referrer']) ? esc_url_raw($data['referrer']) : null,
            'created_at' => current_time('mysql'),
        );

        $wpdb->insert($table_name, $insert_data);

        return $wpdb->insert_id;
    }

    /**
     * Get logs with pagination and filters
     */
    public static function get_logs($args = array()) {
        global $wpdb;

        $defaults = array(
            'type' => '',
            'lead_id' => 0,
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = self::get_table_name();

        $where = array('1=1');
        $values = array();

        if (!empty($args['type'])) {
            $where[] = 'type = %s';
            $values[] = $args['type'];
        }

        if (!empty($args['lead_id'])) {
            $where[] = 'lead_id = %d';
            $values[] = $args['lead_id'];
        }

        if (!empty($args['search'])) {
            $where[] = '(message LIKE %s OR request_data LIKE %s OR response_data LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }

        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = implode(' AND ', $where);

        // Sanitize orderby
        $allowed_orderby = array('id', 'type', 'lead_id', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
        if (!empty($values)) {
            $count_query = $wpdb->prepare($count_query, $values);
        }
        $total = $wpdb->get_var($count_query);

        // Get results
        $offset = ($args['page'] - 1) * $args['per_page'];
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $values[] = $args['per_page'];
        $values[] = $offset;

        $results = $wpdb->get_results($wpdb->prepare($query, $values));

        return array(
            'logs' => $results,
            'total' => intval($total),
            'pages' => ceil($total / $args['per_page']),
            'current_page' => $args['page'],
        );
    }

    /**
     * Get logs by lead ID
     */
    public static function get_logs_by_lead($lead_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE lead_id = %d ORDER BY created_at DESC",
            $lead_id
        ));
    }

    /**
     * Get single log
     */
    public static function get_log($log_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $log_id
        ));
    }

    /**
     * Delete logs older than X days
     */
    public static function delete_old_logs($days = 90) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < %s",
            $date
        ));
    }

    /**
     * Delete specific log
     */
    public static function delete_log($log_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->delete($table_name, array('id' => $log_id), array('%d'));
    }

    /**
     * Get log counts by type
     */
    public static function get_log_counts() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $results = $wpdb->get_results(
            "SELECT type, COUNT(*) as count FROM $table_name GROUP BY type"
        );

        $counts = array();
        foreach ($results as $row) {
            $counts[$row->type] = intval($row->count);
        }

        return $counts;
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get type label
     */
    public static function get_type_label($type) {
        $labels = array(
            self::TYPE_FORM_SUBMIT => 'Formulário Enviado',
            self::TYPE_LEAD_CREATED => 'Lead Criado',
            self::TYPE_HUBSPOT_SYNC => 'Sincronizado HubSpot',
            self::TYPE_HUBSPOT_ERROR => 'Erro HubSpot',
            self::TYPE_HUBSPOT_RETRY => 'Retry HubSpot',
            self::TYPE_SYSTEM => 'Sistema',
        );

        return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
    }

    /**
     * Get type badge class
     */
    public static function get_type_badge_class($type) {
        $classes = array(
            self::TYPE_FORM_SUBMIT => 'info',
            self::TYPE_LEAD_CREATED => 'success',
            self::TYPE_HUBSPOT_SYNC => 'success',
            self::TYPE_HUBSPOT_ERROR => 'danger',
            self::TYPE_HUBSPOT_RETRY => 'warning',
            self::TYPE_SYSTEM => 'secondary',
        );

        return isset($classes[$type]) ? $classes[$type] : 'secondary';
    }
}
