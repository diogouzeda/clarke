<?php
/**
 * HubSpot Integration Class
 * Integração direta via API do HubSpot usando Private App Token
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_HubSpot_Integration {

    /**
     * Option keys
     */
    const OPTION_ACCESS_TOKEN = 'clarke_hubspot_access_token';
    const OPTION_ENABLED = 'clarke_hubspot_enabled';
    const OPTION_PROPERTY_MAPPING = 'clarke_hubspot_property_mapping';

    /**
     * HubSpot API Base URL
     */
    const API_BASE_URL = 'https://api.hubapi.com';

    /**
     * Calculator fields available for mapping
     */
    public static function get_calculator_fields() {
        return array(
            'email' => 'Email',
            'company_type' => 'Tipo de Empresa',
            'company_size' => 'Porte da Empresa',
            'monthly_expense' => 'Gasto Mensal (R$)',
            'recommended_strategy' => 'Estratégia Recomendada',
            'scores' => 'Scores (JSON)',
            'all_answers' => 'Todas as Respostas (JSON)',
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'utm_term' => 'UTM Term',
            'utm_content' => 'UTM Content',
        );
    }

    /**
     * Default property mapping
     */
    public static function get_default_mapping() {
        return array(
            'email' => 'email',
            'company_type' => '',
            'company_size' => '',
            'monthly_expense' => '',
            'recommended_strategy' => '',
            'scores' => '',
            'all_answers' => '',
            'utm_source' => 'hs_analytics_source',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_term' => '',
            'utm_content' => '',
        );
    }

    /**
     * Get property mapping
     */
    public static function get_property_mapping() {
        $mapping = get_option(self::OPTION_PROPERTY_MAPPING, array());
        return wp_parse_args($mapping, self::get_default_mapping());
    }

    /**
     * Save property mapping
     */
    public static function save_property_mapping($mapping) {
        update_option(self::OPTION_PROPERTY_MAPPING, $mapping);
    }

    /**
     * Get HubSpot contact properties
     */
    public static function get_hubspot_properties($access_token = null) {
        if ($access_token === null) {
            $access_token = self::get_access_token();
        }

        if (empty($access_token)) {
            return array();
        }

        // Check cache first
        $cached = get_transient('clarke_hubspot_properties');
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get(self::API_BASE_URL . '/crm/v3/properties/contacts', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return array();
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $properties = array();

        if (isset($body['results']) && is_array($body['results'])) {
            foreach ($body['results'] as $prop) {
                // Only include writable properties
                if (!isset($prop['modificationMetadata']['readOnlyValue']) || !$prop['modificationMetadata']['readOnlyValue']) {
                    $properties[$prop['name']] = array(
                        'name' => $prop['name'],
                        'label' => $prop['label'],
                        'type' => $prop['type'],
                        'groupName' => isset($prop['groupName']) ? $prop['groupName'] : '',
                    );
                }
            }
        }

        // Sort by label
        uasort($properties, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        // Cache for 1 hour
        set_transient('clarke_hubspot_properties', $properties, HOUR_IN_SECONDS);

        return $properties;
    }

    /**
     * Clear properties cache
     */
    public static function clear_properties_cache() {
        delete_transient('clarke_hubspot_properties');
    }

    /**
     * Get Access Token
     */
    public static function get_access_token() {
        return get_option(self::OPTION_ACCESS_TOKEN, '');
    }

    /**
     * Check if integration is enabled
     */
    public static function is_enabled() {
        return get_option(self::OPTION_ENABLED, false) 
            && !empty(self::get_access_token());
    }

    /**
     * Save settings
     */
    public static function save_settings($access_token, $enabled) {
        update_option(self::OPTION_ACCESS_TOKEN, sanitize_text_field($access_token));
        update_option(self::OPTION_ENABLED, (bool) $enabled);
    }

    /**
     * Test connection to HubSpot API
     */
    public static function test_connection($access_token = null) {
        if ($access_token === null) {
            $access_token = self::get_access_token();
        }

        if (empty($access_token)) {
            return array(
                'success' => false,
                'message' => 'Token de Acesso não configurado',
            );
        }

        // Test by getting contacts (requires crm.objects.contacts.read scope)
        $response = wp_remote_get(self::API_BASE_URL . '/crm/v3/objects/contacts?limit=1', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Erro de conexão: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 200) {
            return array(
                'success' => true,
                'message' => 'Conexão com HubSpot estabelecida com sucesso!',
            );
        } elseif ($code === 401) {
            return array(
                'success' => false,
                'message' => 'Token de Acesso inválido ou expirado',
            );
        } elseif ($code === 403) {
            return array(
                'success' => false,
                'message' => 'Token sem permissões necessárias. Verifique os escopos do Private App (crm.objects.contacts.read, crm.objects.contacts.write)',
            );
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return array(
                'success' => false,
                'message' => 'Erro: ' . (isset($body['message']) ? $body['message'] : 'Código ' . $code),
            );
        }
    }

    /**
     * Search for existing contact by email
     */
    private static function search_contact_by_email($email, $access_token) {
        $response = wp_remote_post(self::API_BASE_URL . '/crm/v3/objects/contacts/search', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'filterGroups' => array(
                    array(
                        'filters' => array(
                            array(
                                'propertyName' => 'email',
                                'operator' => 'EQ',
                                'value' => $email,
                            ),
                        ),
                    ),
                ),
                'limit' => 1,
            )),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['results']) && count($body['results']) > 0) {
            return $body['results'][0];
        }

        return null;
    }

    /**
     * Create a new contact in HubSpot
     */
    private static function create_contact($properties, $access_token) {
        $response = wp_remote_post(self::API_BASE_URL . '/crm/v3/objects/contacts', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'properties' => $properties,
            )),
            'timeout' => 15,
        ));

        return $response;
    }

    /**
     * Update an existing contact in HubSpot
     */
    private static function update_contact($contact_id, $properties, $access_token) {
        $response = wp_remote_request(self::API_BASE_URL . '/crm/v3/objects/contacts/' . $contact_id, array(
            'method' => 'PATCH',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'properties' => $properties,
            )),
            'timeout' => 15,
        ));

        return $response;
    }

    /**
     * Sync contact to HubSpot
     */
    public static function sync_contact($lead_data) {
        if (!self::is_enabled()) {
            return array(
                'success' => false,
                'message' => 'Integração HubSpot desabilitada',
                'skipped' => true,
            );
        }

        $access_token = self::get_access_token();

        if (empty($lead_data['email'])) {
            return array(
                'success' => false,
                'message' => 'Email é obrigatório',
            );
        }

        // Get property mapping
        $mapping = self::get_property_mapping();

        // Prepare properties based on mapping
        $properties = array(
            'email' => $lead_data['email'],
        );

        // Apply mapping for each calculator field
        foreach ($mapping as $calc_field => $hubspot_property) {
            if (empty($hubspot_property) || $calc_field === 'email') {
                continue;
            }

            $value = isset($lead_data[$calc_field]) ? $lead_data[$calc_field] : '';
            
            if (empty($value)) {
                continue;
            }

            // Handle special formatting
            if ($calc_field === 'scores' || $calc_field === 'all_answers') {
                // JSON fields - convert to readable string if it's an array
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $value = self::format_array_for_hubspot($decoded);
                    }
                } elseif (is_array($value)) {
                    $value = self::format_array_for_hubspot($value);
                }
            } elseif ($calc_field === 'monthly_expense') {
                // Format as currency
                $value = 'R$ ' . number_format((float)$value, 2, ',', '.');
            }

            $properties[$hubspot_property] = $value;
        }

        // Set default HubSpot properties
        $properties['hs_lead_status'] = 'NEW';
        $properties['lifecyclestage'] = 'lead';

        try {
            // Search for existing contact
            $existing_contact = self::search_contact_by_email($lead_data['email'], $access_token);
            $action = 'created';
            $contact_id = null;

            if ($existing_contact) {
                // Update existing contact
                $contact_id = $existing_contact['id'];
                $response = self::update_contact($contact_id, $properties, $access_token);
                $action = 'updated';
            } else {
                // Create new contact
                $response = self::create_contact($properties, $access_token);
            }

            if (is_wp_error($response)) {
                Clarke_Logger::log(
                    Clarke_Logger::TYPE_HUBSPOT_ERROR,
                    'Erro de conexão: ' . $response->get_error_message(),
                    array(
                        'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                        'email' => $lead_data['email'],
                    )
                );

                return array(
                    'success' => false,
                    'message' => 'Erro de conexão: ' . $response->get_error_message(),
                );
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);

            if ($code === 200 || $code === 201) {
                $contact_id = isset($body['id']) ? $body['id'] : $contact_id;

                Clarke_Logger::log(
                    Clarke_Logger::TYPE_HUBSPOT_SYNC,
                    'Contato ' . $action . ' com sucesso',
                    array(
                        'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                        'email' => $lead_data['email'],
                        'hubspot_contact_id' => $contact_id,
                        'action' => $action,
                    )
                );

                return array(
                    'success' => true,
                    'message' => 'Contato ' . ($action === 'created' ? 'criado' : 'atualizado') . ' com sucesso',
                    'contact_id' => $contact_id,
                    'action' => $action,
                );
            } elseif ($code === 409) {
                // Conflict - contact already exists (race condition)
                // Try to get the existing contact and update it
                $existing_contact = self::search_contact_by_email($lead_data['email'], $access_token);
                if ($existing_contact) {
                    $contact_id = $existing_contact['id'];
                    $response = self::update_contact($contact_id, $properties, $access_token);
                    
                    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                        Clarke_Logger::log(
                            Clarke_Logger::TYPE_HUBSPOT_SYNC,
                            'Contato atualizado com sucesso (após conflito)',
                            array(
                                'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                                'email' => $lead_data['email'],
                                'hubspot_contact_id' => $contact_id,
                            )
                        );

                        return array(
                            'success' => true,
                            'message' => 'Contato atualizado com sucesso',
                            'contact_id' => $contact_id,
                            'action' => 'updated',
                        );
                    }
                }
            }

            $error_message = isset($body['message']) ? $body['message'] : 'Erro desconhecido (código ' . $code . ')';

            Clarke_Logger::log(
                Clarke_Logger::TYPE_HUBSPOT_ERROR,
                'Erro ao sincronizar: ' . $error_message,
                array(
                    'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                    'email' => $lead_data['email'],
                    'response_code' => $code,
                    'response_body' => $body,
                )
            );

            return array(
                'success' => false,
                'message' => $error_message,
                'code' => $code,
            );

        } catch (Exception $e) {
            Clarke_Logger::log(
                Clarke_Logger::TYPE_HUBSPOT_ERROR,
                'Exceção: ' . $e->getMessage(),
                array(
                    'lead_id' => isset($lead_data['id']) ? $lead_data['id'] : null,
                    'email' => $lead_data['email'],
                )
            );

            return array(
                'success' => false,
                'message' => 'Exceção: ' . $e->getMessage(),
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
     * Format array data for HubSpot (convert to readable string)
     */
    private static function format_array_for_hubspot($data, $prefix = '') {
        $lines = array();
        
        foreach ($data as $key => $value) {
            $label = $prefix ? "$prefix.$key" : $key;
            
            if (is_array($value)) {
                $lines[] = self::format_array_for_hubspot($value, $label);
            } else {
                $lines[] = "$label: $value";
            }
        }
        
        return implode("\n", $lines);
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
