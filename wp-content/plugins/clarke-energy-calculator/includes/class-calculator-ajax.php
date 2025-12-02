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

        // Get and validate email
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Por favor, informe um email válido.'));
        }

        // Get other data
        $answers = isset($_POST['answers']) ? $this->sanitize_answers($_POST['answers']) : array();
        $scores = isset($_POST['scores']) ? $_POST['scores'] : array();
        $recommended_strategy = isset($_POST['recommended_strategy']) ? sanitize_text_field($_POST['recommended_strategy']) : '';

        // Prepare data
        $company_type = isset($answers['company_type']) ? $answers['company_type'] : '';
        $company_size = isset($answers['company_size']) ? $answers['company_size'] : '';
        $monthly_expense = isset($answers['monthly_expense']) ? $answers['monthly_expense'] : '';

        // Insert into database
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'email' => $email,
                'company_type' => $company_type,
                'company_size' => $company_size,
                'monthly_expense' => $monthly_expense,
                'recommended_strategy' => $recommended_strategy,
                'all_answers' => json_encode($answers),
                'scores' => json_encode($scores),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Erro ao salvar dados. Tente novamente.'));
        }

        // Optional: Send notification email
        $this->send_notification_email($email, $answers, $recommended_strategy);

        wp_send_json_success(array('message' => 'Lead salvo com sucesso!'));
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
}
