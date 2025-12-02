<?php
/**
 * HubSpot Integration Class
 * Integração via HubSpot App (Public Function)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_HubSpot_Integration {

    /**
     * Option keys
     */
    const OPTION_ENDPOINT_URL = 'clarke_hubspot_endpoint_url';
    const OPTION_API_SECRET = 'clarke_hubspot_api_secret';
    const OPTION_ENABLED = 'clarke_hubspot_enabled';

    /**
     * Get Endpoint URL
     */
    public static function get_endpoint_url() {
        return get_option(self::OPTION_ENDPOINT_URL, '');
    }

    /**
     * Get API Secret
     */
    public static function get_api_secret() {
        return get_option(self::OPTION_API_SECRET, '');
    }

    /**
     * Check if integration is enabled
     */
    public static function is_enabled() {
        return get_option(self::OPTION_ENABLED, false) 
            && !empty(self::get_endpoint_url()) 
            && !empty(self::get_api_secret());
    }

    /**
     * Save settings
     */
    public static function save_settings($endpoint_url, $api_secret, $enabled) {
        update_option(self::OPTION_ENDPOINT_URL, esc_url_raw($endpoint_url));
        update_option(self::OPTION_API_SECRET, sanitize_text_field($api_secret));
        update_option(self::OPTION_ENABLED, (bool) $enabled);
    }

    /**
     * Test connection to HubSpot App
     */
    public static function test_connection($endpoint_url = null, $api_secret = null) {
        if ($endpoint_url === null) {
            $endpoint_url = self::get_endpoint_url();
        }
        if ($api_secret === null) {
            $api_secret = self::get_api_secret();
        }

        if (empty($endpoint_url)) {
            return array(
                'success' => false,
                'message' => 'URL do Endpoint não configurada',
            );
        }

        if (empty($api_secret)) {
            return array(
                'success' => false,
                'message' => 'API Secret não configurado',
            );
        }

        // Send test request
        $response = wp_remote_post($endpoint_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_secret,
            ),
            'body' => wp_json_encode(array(
                'test' => true,
                'email' => 'test@teste.com',
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Erro de conexão: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['success']) && $body['success']) {
            return array(
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso! ' . (isset($body['message']) ? $body['message'] : ''),
            );
        } elseif ($code === 401) {
            return array(
                'success' => false,
                'message' => 'API Secret inválido',
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Erro: ' . (isset($body['message']) ? $body['message'] : 'Código ' . $code),
                'response' => $body,
            );
        }
    }

    /**
     * Sync contact to HubSpot via App Function
     */
    public static function sync_contact($lead_data) {
        if (!self::is_enabled()) {
            return array(
                'success' => false,
                'message' => 'Integração HubSpot desabilitada',
                'skipped' => true,
            );
        }

        $endpoint_url = self::get_endpoint_url();
        $api_secret = self::get_api_secret();

        // Prepare payload
        $payload = array(
            'email' => $lead_data['email'],
            'company_type' => isset($lead_data['company_type']) ? $lead_data['company_type'] : '',
            'company_size' => isset($lead_data['company_size']) ? $lead_data['company_size'] : '',
            'monthly_expense' => isset($lead_data['monthly_expense']) ? $lead_data['monthly_expense'] : '',
            'recommended_strategy' => isset($lead_data['recommended_strategy']) ? $lead_data['recommended_strategy'] : '',
            'scores' => isset($lead_data['scores']) ? $lead_data['scores'] : '',
            'all_answers' => isset($lead_data['all_answers']) ? $lead_data['all_answers'] : '',
            'utm_source' => isset($lead_data['utm_source']) ? $lead_data['utm_source'] : '',
            'utm_medium' => isset($lead_data['utm_medium']) ? $lead_data['utm_medium'] : '',
            'utm_campaign' => isset($lead_data['utm_campaign']) ? $lead_data['utm_campaign'] : '',
            'utm_term' => isset($lead_data['utm_term']) ? $lead_data['utm_term'] : '',
            'utm_content' => isset($lead_data['utm_content']) ? $lead_data['utm_content'] : '',
            'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
        );

        // Send to HubSpot App
        $response = wp_remote_post($endpoint_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_secret,
            ),
            'body' => wp_json_encode($payload),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            // Log error
            Clarke_Logger::log(
                Clarke_Logger::TYPE_HUBSPOT_ERROR,
                'Erro de conexão: ' . $response->get_error_message(),
                array(
                    'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                    'request' => $payload,
                )
            );

            return array(
                'success' => false,
                'message' => 'Erro de conexão: ' . $response->get_error_message(),
                'request' => $payload,
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['success']) && $body['success']) {
            // Log success
            Clarke_Logger::log(
                Clarke_Logger::TYPE_HUBSPOT_SYNC,
                'Contato sincronizado: ' . (isset($body['action']) ? $body['action'] : 'synced'),
                array(
                    'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                    'request' => $payload,
                    'response' => $body,
                )
            );

            return array(
                'success' => true,
                'message' => isset($body['message']) ? $body['message'] : 'Sincronizado',
                'contact_id' => isset($body['contact_id']) ? $body['contact_id'] : null,
                'action' => isset($body['action']) ? $body['action'] : 'synced',
                'request' => $payload,
                'response' => $body,
            );
        } else {
            $error_message = isset($body['message']) ? $body['message'] : 'Erro desconhecido (código ' . $code . ')';

            // Log error
            Clarke_Logger::log(
                Clarke_Logger::TYPE_HUBSPOT_ERROR,
                'Erro ao sincronizar: ' . $error_message,
                array(
                    'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                    'request' => $payload,
                    'response' => $body,
                )
            );

            return array(
                'success' => false,
                'message' => $error_message,
                'code' => $code,
                'request' => $payload,
                'response' => $body,
            );
        }
    }

    /**
     * Retry sync for a specific lead
     */
    public static function retry_sync($lead_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $lead_id
        ));

        if (!$lead) {
            return array(
                'success' => false,
                'message' => 'Lead não encontrado',
            );
        }

        // Prepare lead data
        $lead_data = array(
            'id' => $lead->id,
            'email' => $lead->email,
            'company_type' => $lead->company_type,
            'company_size' => $lead->company_size,
            'monthly_expense' => $lead->monthly_expense,
            'recommended_strategy' => $lead->recommended_strategy,
            'scores' => $lead->scores,
            'all_answers' => $lead->all_answers,
            'utm_source' => isset($lead->utm_source) ? $lead->utm_source : '',
            'utm_medium' => isset($lead->utm_medium) ? $lead->utm_medium : '',
            'utm_campaign' => isset($lead->utm_campaign) ? $lead->utm_campaign : '',
            'utm_term' => isset($lead->utm_term) ? $lead->utm_term : '',
            'utm_content' => isset($lead->utm_content) ? $lead->utm_content : '',
        );

        // Sync to HubSpot
        $result = self::sync_contact($lead_data);

        // Update lead if successful
        if ($result['success'] && isset($result['contact_id'])) {
            $wpdb->update(
                $table_name,
                array(
                    'hubspot_contact_id' => $result['contact_id'],
                    'hubspot_synced_at' => current_time('mysql'),
                ),
                array('id' => $lead_id),
                array('%s', '%s'),
                array('%d')
            );
        }

        return $result;
    }

    /**
     * Get leads pending sync
     */
    public static function get_pending_leads() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'clarke_calculator_leads';

        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE hubspot_contact_id IS NULL OR hubspot_contact_id = '' ORDER BY created_at DESC LIMIT 100"
        );
    }
}
