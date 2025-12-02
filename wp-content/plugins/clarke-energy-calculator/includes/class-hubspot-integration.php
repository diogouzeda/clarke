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

    /**
     * HubSpot API Base URL
     */
    const API_BASE_URL = 'https://api.hubapi.com';

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

        // Prepare properties
        $properties = array(
            'email' => $lead_data['email'],
        );

        // Map calculator fields to HubSpot properties
        // Campos padrão do HubSpot
        if (!empty($lead_data['firstname'])) {
            $properties['firstname'] = $lead_data['firstname'];
        }
        if (!empty($lead_data['lastname'])) {
            $properties['lastname'] = $lead_data['lastname'];
        }
        if (!empty($lead_data['phone'])) {
            $properties['phone'] = $lead_data['phone'];
        }
        if (!empty($lead_data['company'])) {
            $properties['company'] = $lead_data['company'];
        }

        // Campos personalizados da calculadora (você pode criar esses campos no HubSpot)
        // Por enquanto, vamos salvar tudo no campo "message" ou criar campos customizados
        $calculator_info = array();
        
        if (!empty($lead_data['company_type'])) {
            $calculator_info[] = 'Tipo de Empresa: ' . $lead_data['company_type'];
        }
        if (!empty($lead_data['company_size'])) {
            $calculator_info[] = 'Porte: ' . $lead_data['company_size'];
        }
        if (!empty($lead_data['monthly_expense'])) {
            $calculator_info[] = 'Gasto Mensal: R$ ' . number_format((float)$lead_data['monthly_expense'], 2, ',', '.');
        }
        if (!empty($lead_data['recommended_strategy'])) {
            $calculator_info[] = 'Estratégia Recomendada: ' . $lead_data['recommended_strategy'];
        }
        if (!empty($lead_data['scores'])) {
            $scores = is_string($lead_data['scores']) ? json_decode($lead_data['scores'], true) : $lead_data['scores'];
            if (is_array($scores)) {
                $calculator_info[] = 'Scores: ' . implode(', ', array_map(function($k, $v) {
                    return "$k: $v";
                }, array_keys($scores), $scores));
            }
        }

        if (!empty($calculator_info)) {
            // Usar o campo message para armazenar info da calculadora
            // Você pode criar campos customizados no HubSpot para dados mais estruturados
            $properties['message'] = "Lead da Calculadora de Energia Clarke\n\n" . implode("\n", $calculator_info);
        }

        // UTM Parameters
        if (!empty($lead_data['utm_source'])) {
            $properties['hs_analytics_source'] = $lead_data['utm_source'];
        }

        // Lead source
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
