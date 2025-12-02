<?php
/**
 * Lead Manager Class
 * Handles lead storage and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_Lead_Manager {

    /**
     * Get all leads
     */
    public static function get_leads($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';

        $sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );

        return $wpdb->get_results($sql);
    }

    /**
     * Get lead by ID
     */
    public static function get_lead($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id)
        );
    }

    /**
     * Delete lead
     */
    public static function delete_lead($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }

    /**
     * Get total leads count
     */
    public static function get_total_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }

    /**
     * Export leads to CSV
     */
    public static function export_csv() {
        $leads = self::get_leads(array('limit' => 10000));
        
        $csv_data = array();
        $csv_data[] = array('ID', 'Email', 'Tipo Empresa', 'Porte', 'Gasto Mensal', 'Estratégia Recomendada', 'Data');

        foreach ($leads as $lead) {
            $csv_data[] = array(
                $lead->id,
                $lead->email,
                $lead->company_type,
                $lead->company_size,
                $lead->monthly_expense,
                $lead->recommended_strategy,
                $lead->created_at,
            );
        }

        return $csv_data;
    }
}
