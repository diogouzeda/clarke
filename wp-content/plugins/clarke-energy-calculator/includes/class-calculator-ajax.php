<?php
/**
 * AJAX Handler Class
 * Handles all AJAX requests for the calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_Calculator_Ajax {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_clarke_calculate', array($this, 'calculate'));
        add_action('wp_ajax_nopriv_clarke_calculate', array($this, 'calculate'));
        
        add_action('wp_ajax_clarke_save_lead', array($this, 'save_lead'));
        add_action('wp_ajax_nopriv_clarke_save_lead', array($this, 'save_lead'));
        
        add_action('wp_ajax_clarke_request_analysis', array($this, 'request_analysis'));
        add_action('wp_ajax_nopriv_clarke_request_analysis', array($this, 'request_analysis'));
    }

    /**
     * Calculate strategy scores
     */
    public function calculate() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clarke_calculator_nonce')) {
            wp_send_json_error(array('message' => 'Erro de segurança. Recarregue a página.'));
        }

        // Get answers
        $answers = isset($_POST['answers']) ? $this->sanitize_answers($_POST['answers']) : array();

        if (empty($answers)) {
            wp_send_json_error(array('message' => 'Nenhuma resposta recebida.'));
        }

        // Calculate results
        $calculator = new Clarke_Calculator_Logic();
        $results = $calculator->get_full_results($answers);

        wp_send_json_success($results);
    }

    /**
     * Save lead to database
     */
    public function save_lead() {
        global $wpdb;

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clarke_calculator_nonce')) {
            wp_send_json_error(array('message' => 'Erro de segurança. Recarregue a página.'));
        }

        // Get and validate name
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        
        if (empty($name)) {
            wp_send_json_error(array('message' => 'Por favor, informe seu nome.'));
        }

        // Get and validate email
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Por favor, informe um email válido.'));
        }

        // Get other data
        $answers = isset($_POST['answers']) ? $this->sanitize_answers($_POST['answers']) : array();
        $scores = isset($_POST['scores']) ? $_POST['scores'] : array();
        $recommended_strategy = isset($_POST['recommended_strategy']) ? sanitize_text_field($_POST['recommended_strategy']) : '';

        // Get UTM and tracking data
        $utm_source = isset($_POST['utm_source']) ? sanitize_text_field($_POST['utm_source']) : '';
        $utm_medium = isset($_POST['utm_medium']) ? sanitize_text_field($_POST['utm_medium']) : '';
        $utm_campaign = isset($_POST['utm_campaign']) ? sanitize_text_field($_POST['utm_campaign']) : '';
        $utm_term = isset($_POST['utm_term']) ? sanitize_text_field($_POST['utm_term']) : '';
        $utm_content = isset($_POST['utm_content']) ? sanitize_text_field($_POST['utm_content']) : '';
        $referrer = isset($_POST['referrer']) ? esc_url_raw($_POST['referrer']) : '';
        $lead_source = isset($_POST['lead_source']) ? sanitize_text_field($_POST['lead_source']) : '';
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        $page_title = isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';
        
        // Get IP and User Agent
        $ip_address = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';

        // Prepare data
        $company_type = isset($answers['company_type']) ? $answers['company_type'] : '';
        $company_size = isset($answers['company_size']) ? $answers['company_size'] : '';
        $monthly_expense = isset($answers['monthly_expense']) ? $answers['monthly_expense'] : '';

        // Insert into database
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        $lead_data = array(
            'name' => $name,
            'email' => $email,
            'company_type' => $company_type,
            'company_size' => $company_size,
            'monthly_expense' => $monthly_expense,
            'recommended_strategy' => $recommended_strategy,
            'all_answers' => json_encode($answers),
            'scores' => json_encode($scores),
            'lead_source' => $lead_source,
            'page_url' => $page_url,
            'page_title' => $page_title,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'utm_term' => $utm_term,
            'utm_content' => $utm_content,
            'referrer' => $referrer,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => current_time('mysql'),
        );
        
        $result = $wpdb->insert(
            $table_name,
            $lead_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            // Log error
            Clarke_Logger::log('lead_error', 'Erro ao salvar lead no banco de dados', array(
                'request' => $lead_data,
                'response' => array('error' => $wpdb->last_error)
            ));
            wp_send_json_error(array('message' => 'Erro ao salvar dados. Tente novamente.'));
        }

        $lead_id = $wpdb->insert_id;

        // Log form submission
        Clarke_Logger::log('form_submit', 'Formulário enviado com sucesso', array(
            'lead_id' => $lead_id,
            'request' => $lead_data,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'utm_term' => $utm_term,
            'utm_content' => $utm_content,
            'referrer' => $referrer
        ));

        // Log lead creation
        Clarke_Logger::log('lead_created', "Lead #{$lead_id} criado: {$email}", array(
            'lead_id' => $lead_id
        ));

        // Sync with HubSpot
        $hubspot_synced = false;
        if (Clarke_HubSpot_Integration::is_enabled()) {
            $lead_data['id'] = $lead_id;
            $hubspot_result = Clarke_HubSpot_Integration::sync_contact($lead_data);
            
            if ($hubspot_result['success']) {
                $hubspot_synced = true;
                
                // Update lead with HubSpot contact ID
                $wpdb->update(
                    $table_name,
                    array(
                        'hubspot_contact_id' => $hubspot_result['contact_id'],
                        'hubspot_synced_at' => current_time('mysql'),
                    ),
                    array('id' => $lead_id),
                    array('%s', '%s'),
                    array('%d')
                );
            }
        }

        // Send notification email
        $this->send_notification_email($email, $answers, $recommended_strategy);

        wp_send_json_success(array(
            'message' => 'Lead salvo com sucesso!',
            'lead_id' => $lead_id,
            'hubspot_synced' => $hubspot_synced,
        ));
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field(trim($ip));
    }

    /**
     * Sanitize answers array
     */
    private function sanitize_answers($answers) {
        if (!is_array($answers)) {
            return array();
        }

        $sanitized = array();
        foreach ($answers as $key => $value) {
            $sanitized[sanitize_key($key)] = sanitize_text_field($value);
        }

        return $sanitized;
    }

    /**
     * Send notification email (optional)
     */
    private function send_notification_email($email, $answers, $strategy) {
        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Build email content
        $subject = 'Novo Lead - Calculadora de Energia Clarke';
        
        $calculator = new Clarke_Calculator_Logic();
        $strategy_name = $calculator->get_strategy_name($strategy);
        
        $company_types = array(
            'industria' => 'Indústria',
            'comercio' => 'Comércio',
            'servicos' => 'Serviços',
            'agronegocio' => 'Agronegócio',
            'hospital_saude' => 'Hospital/Saúde',
            'educacao' => 'Educação',
        );

        $company_sizes = array(
            'pequena' => 'Pequena (até 30 kW)',
            'media' => 'Média (30-500 kW)',
            'grande' => 'Grande (>500 kW)',
        );

        $expenses = array(
            'ate_5000' => 'Até R$ 5.000',
            '5000_15000' => 'R$ 5.000 - R$ 15.000',
            '15000_50000' => 'R$ 15.000 - R$ 50.000',
            'acima_50000' => 'Acima de R$ 50.000',
        );

        $company_type = isset($answers['company_type']) && isset($company_types[$answers['company_type']]) 
            ? $company_types[$answers['company_type']] 
            : '-';
        
        $company_size = isset($answers['company_size']) && isset($company_sizes[$answers['company_size']]) 
            ? $company_sizes[$answers['company_size']] 
            : '-';
        
        $monthly_expense = isset($answers['monthly_expense']) && isset($expenses[$answers['monthly_expense']]) 
            ? $expenses[$answers['monthly_expense']] 
            : '-';

        $message = "Novo lead gerado pela Calculadora de Energia:\n\n";
        $message .= "Email: {$email}\n";
        $message .= "Tipo de Empresa: {$company_type}\n";
        $message .= "Porte: {$company_size}\n";
        $message .= "Gasto Mensal: {$monthly_expense}\n";
        $message .= "Estratégia Recomendada: {$strategy_name}\n\n";
        $message .= "Data: " . current_time('d/m/Y H:i') . "\n";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Handle analysis request for ACL strategies
     */
    public function request_analysis() {
        global $wpdb;

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clarke_calculator_nonce')) {
            wp_send_json_error(array('message' => 'Erro de segurança. Recarregue a página.'));
        }

        // Get and validate phone
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        
        if (empty($phone)) {
            wp_send_json_error(array('message' => 'Por favor, informe seu telefone.'));
        }

        // Get other data
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $strategy = isset($_POST['strategy']) ? sanitize_text_field($_POST['strategy']) : '';

        // Strategy names mapping
        $strategy_names = array(
            'acl' => 'ACL (Mercado Livre de Energia)',
            'acl_solar' => 'ACL + Solar (Híbrido)',
            'biomassa_acl' => 'Biomassa + ACL',
        );

        $strategy_name = isset($strategy_names[$strategy]) ? $strategy_names[$strategy] : $strategy;

        // Log the analysis request
        Clarke_Logger::log(
            'analysis_request',
            "Solicitação de análise para {$strategy_name}",
            array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'strategy' => $strategy,
            )
        );

        // Send notification email to admin
        $admin_email = get_option('admin_email');
        $subject = "[Clarke Energia] Solicitação de Análise - {$strategy_name}";
        
        $message = "Nova solicitação de análise detalhada:\n\n";
        $message .= "Nome: {$name}\n";
        $message .= "Email: {$email}\n";
        $message .= "Telefone: {$phone}\n";
        $message .= "Estratégia: {$strategy_name}\n\n";
        $message .= "Data: " . current_time('d/m/Y H:i') . "\n\n";
        $message .= "Este lead solicitou uma análise detalhada para migração ao Mercado Livre de Energia ou estratégia híbrida.";

        wp_mail($admin_email, $subject, $message);

        // Update lead phone in database if exists
        $leads_table = $wpdb->prefix . 'clarke_calculator_leads';
        
        if (!empty($email)) {
            // Find the most recent lead with this email
            $lead = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM $leads_table WHERE email = %s ORDER BY created_at DESC LIMIT 1",
                    $email
                )
            );
            
            if ($lead) {
                // Add phone to all_answers JSON
                $current = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT all_answers FROM $leads_table WHERE id = %d",
                        $lead->id
                    )
                );
                
                $answers = json_decode($current, true) ?: array();
                $answers['analysis_phone'] = $phone;
                $answers['analysis_requested'] = true;
                $answers['analysis_requested_at'] = current_time('mysql');
                
                $wpdb->update(
                    $leads_table,
                    array('all_answers' => json_encode($answers)),
                    array('id' => $lead->id),
                    array('%s'),
                    array('%d')
                );
            }
        }

        wp_send_json_success(array(
            'message' => 'Solicitação enviada com sucesso!'
        ));
    }
}
