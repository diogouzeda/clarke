<?php
/**
 * Admin Settings Class
 * Handles the plugin admin pages and settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_Admin_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_clarke_test_hubspot', array($this, 'ajax_test_hubspot'));
        add_action('wp_ajax_clarke_retry_hubspot', array($this, 'ajax_retry_hubspot'));
        add_action('wp_ajax_clarke_get_log_details', array($this, 'ajax_get_log_details'));
        add_action('wp_ajax_clarke_refresh_properties', array($this, 'ajax_refresh_properties'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Clarke Calculator',
            'Clarke Calculator',
            'manage_options',
            'clarke-calculator',
            array($this, 'render_admin_page'),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'clarke-calculator',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'clarke-calculator',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'clarke-calculator',
            'Leads',
            'Leads',
            'manage_options',
            'clarke-calculator-leads',
            array($this, 'render_leads_page')
        );

        add_submenu_page(
            'clarke-calculator',
            'Logs',
            'Logs',
            'manage_options',
            'clarke-calculator-logs',
            array($this, 'render_logs_page')
        );

        add_submenu_page(
            'clarke-calculator',
            'HubSpot',
            'HubSpot',
            'manage_options',
            'clarke-calculator-hubspot',
            array($this, 'render_hubspot_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('clarke_hubspot_settings', Clarke_HubSpot_Integration::OPTION_ACCESS_TOKEN);
        register_setting('clarke_hubspot_settings', Clarke_HubSpot_Integration::OPTION_ENABLED);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'clarke-calculator') === false) {
            return;
        }

        wp_enqueue_style(
            'clarke-admin-style',
            CLARKE_CALC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CLARKE_CALC_VERSION
        );

        wp_enqueue_script(
            'clarke-admin-script',
            CLARKE_CALC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            CLARKE_CALC_VERSION,
            true
        );

        wp_localize_script('clarke-admin-script', 'clarkeAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('clarke_admin_nonce'),
        ));
    }

    /**
     * Render main admin page (Dashboard)
     */
    public function render_admin_page() {
        $this->render_page_header('Dashboard');
        
        // Get stats
        global $wpdb;
        $leads_table = $wpdb->prefix . 'clarke_calculator_leads';
        
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table");
        $today_leads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $leads_table WHERE DATE(created_at) = %s",
            current_time('Y-m-d')
        ));
        
        $synced_leads = $wpdb->get_var(
            "SELECT COUNT(*) FROM $leads_table WHERE hubspot_contact_id IS NOT NULL AND hubspot_contact_id != ''"
        );
        
        $pending_leads = $total_leads - $synced_leads;
        
        $log_counts = Clarke_Logger::get_log_counts();
        $hubspot_errors = isset($log_counts[Clarke_Logger::TYPE_HUBSPOT_ERROR]) ? $log_counts[Clarke_Logger::TYPE_HUBSPOT_ERROR] : 0;
        
        ?>
        <div class="clarke-admin-content">
            <div class="clarke-stats-grid">
                <div class="clarke-stat-card">
                    <div class="clarke-stat-icon leads">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="clarke-stat-info">
                        <span class="clarke-stat-number"><?php echo esc_html($total_leads); ?></span>
                        <span class="clarke-stat-label">Total de Leads</span>
                    </div>
                </div>
                
                <div class="clarke-stat-card">
                    <div class="clarke-stat-icon today">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="clarke-stat-info">
                        <span class="clarke-stat-number"><?php echo esc_html($today_leads); ?></span>
                        <span class="clarke-stat-label">Leads Hoje</span>
                    </div>
                </div>
                
                <div class="clarke-stat-card">
                    <div class="clarke-stat-icon synced">
                        <span class="dashicons dashicons-cloud-saved"></span>
                    </div>
                    <div class="clarke-stat-info">
                        <span class="clarke-stat-number"><?php echo esc_html($synced_leads); ?></span>
                        <span class="clarke-stat-label">Sincronizados HubSpot</span>
                    </div>
                </div>
                
                <div class="clarke-stat-card">
                    <div class="clarke-stat-icon pending">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <div class="clarke-stat-info">
                        <span class="clarke-stat-number"><?php echo esc_html($pending_leads); ?></span>
                        <span class="clarke-stat-label">Pendentes Sync</span>
                    </div>
                </div>
                
                <?php if ($hubspot_errors > 0): ?>
                <div class="clarke-stat-card warning">
                    <div class="clarke-stat-icon errors">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="clarke-stat-info">
                        <span class="clarke-stat-number"><?php echo esc_html($hubspot_errors); ?></span>
                        <span class="clarke-stat-label">Erros HubSpot</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="clarke-admin-row">
                <div class="clarke-admin-col">
                    <div class="clarke-admin-card">
                        <h3>Últimos Leads</h3>
                        <?php $this->render_recent_leads(5); ?>
                        <p><a href="<?php echo admin_url('admin.php?page=clarke-calculator-leads'); ?>">Ver todos os leads →</a></p>
                    </div>
                </div>
                
                <div class="clarke-admin-col">
                    <div class="clarke-admin-card">
                        <h3>Últimos Logs</h3>
                        <?php $this->render_recent_logs(5); ?>
                        <p><a href="<?php echo admin_url('admin.php?page=clarke-calculator-logs'); ?>">Ver todos os logs →</a></p>
                    </div>
                </div>
            </div>
            
            <div class="clarke-admin-card">
                <h3>Status da Integração HubSpot</h3>
                <?php
                $hubspot_enabled = Clarke_HubSpot_Integration::is_enabled();
                $access_token = Clarke_HubSpot_Integration::get_access_token();
                ?>
                <p>
                    <strong>Status:</strong> 
                    <?php if ($hubspot_enabled): ?>
                        <span class="clarke-badge success">Ativado</span>
                    <?php else: ?>
                        <span class="clarke-badge warning">Desativado</span>
                    <?php endif; ?>
                </p>
                <p>
                    <strong>Token:</strong> 
                    <?php echo !empty($access_token) ? '<span class="clarke-badge success">Configurado</span>' : '<span class="clarke-badge danger">Não configurado</span>'; ?>
                </p>
                <p><a href="<?php echo admin_url('admin.php?page=clarke-calculator-hubspot'); ?>" class="button">Configurar HubSpot</a></p>
            </div>
        </div>
        <?php
        $this->render_page_footer();
    }

    /**
     * Render leads page
     */
    public function render_leads_page() {
        $this->render_page_header('Leads');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Filters
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $sync_status = isset($_GET['sync_status']) ? sanitize_key($_GET['sync_status']) : '';
        
        $where = array('1=1');
        $values = array();
        
        if (!empty($search)) {
            $where[] = 'email LIKE %s';
            $values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if ($sync_status === 'synced') {
            $where[] = "hubspot_contact_id IS NOT NULL AND hubspot_contact_id != ''";
        } elseif ($sync_status === 'pending') {
            $where[] = "(hubspot_contact_id IS NULL OR hubspot_contact_id = '')";
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get total
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
        if (!empty($values)) {
            $count_query = $wpdb->prepare($count_query, $values);
        }
        $total = $wpdb->get_var($count_query);
        $total_pages = ceil($total / $per_page);
        
        // Get leads
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;
        $leads = $wpdb->get_results($wpdb->prepare($query, $values));
        
        ?>
        <div class="clarke-admin-content">
            <div class="clarke-admin-card">
                <div class="clarke-filters">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="clarke-calculator-leads">
                        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por email...">
                        <select name="sync_status">
                            <option value="">Todos os status</option>
                            <option value="synced" <?php selected($sync_status, 'synced'); ?>>Sincronizados</option>
                            <option value="pending" <?php selected($sync_status, 'pending'); ?>>Pendentes</option>
                        </select>
                        <button type="submit" class="button">Filtrar</button>
                    </form>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Tipo Empresa</th>
                            <th>Porte</th>
                            <th>Gasto Mensal</th>
                            <th>Estratégia</th>
                            <th>HubSpot</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                            <tr>
                                <td colspan="9">Nenhum lead encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?php echo esc_html($lead->id); ?></td>
                                    <td><strong><?php echo esc_html($lead->email); ?></strong></td>
                                    <td><?php echo esc_html($lead->company_type); ?></td>
                                    <td><?php echo esc_html($lead->company_size); ?></td>
                                    <td><?php echo esc_html($lead->monthly_expense); ?></td>
                                    <td><span class="clarke-badge info"><?php echo esc_html($lead->recommended_strategy); ?></span></td>
                                    <td>
                                        <?php if (!empty($lead->hubspot_contact_id)): ?>
                                            <span class="clarke-badge success" title="ID: <?php echo esc_attr($lead->hubspot_contact_id); ?>">
                                                Sincronizado
                                            </span>
                                        <?php else: ?>
                                            <span class="clarke-badge warning">Pendente</span>
                                            <button type="button" class="button button-small clarke-retry-hubspot" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                                Retry
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html(date('d/m/Y H:i', strtotime($lead->created_at))); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=clarke-calculator-logs&lead_id=' . $lead->id); ?>" class="button button-small">
                                            Ver Logs
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page,
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $this->render_page_footer();
    }

    /**
     * Render logs page
     */
    public function render_logs_page() {
        $this->render_page_header('Logs');
        
        // Filters
        $args = array(
            'type' => isset($_GET['type']) ? sanitize_key($_GET['type']) : '',
            'lead_id' => isset($_GET['lead_id']) ? intval($_GET['lead_id']) : 0,
            'search' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'page' => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1,
            'per_page' => 30,
        );
        
        $result = Clarke_Logger::get_logs($args);
        $logs = $result['logs'];
        $total_pages = $result['pages'];
        
        ?>
        <div class="clarke-admin-content">
            <div class="clarke-admin-card">
                <div class="clarke-filters">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="clarke-calculator-logs">
                        <?php if ($args['lead_id']): ?>
                            <input type="hidden" name="lead_id" value="<?php echo esc_attr($args['lead_id']); ?>">
                            <span class="clarke-filter-tag">Lead ID: <?php echo esc_html($args['lead_id']); ?> <a href="<?php echo admin_url('admin.php?page=clarke-calculator-logs'); ?>">×</a></span>
                        <?php endif; ?>
                        <select name="type">
                            <option value="">Todos os tipos</option>
                            <option value="form_submit" <?php selected($args['type'], 'form_submit'); ?>>Formulário Enviado</option>
                            <option value="lead_created" <?php selected($args['type'], 'lead_created'); ?>>Lead Criado</option>
                            <option value="hubspot_sync" <?php selected($args['type'], 'hubspot_sync'); ?>>Sincronizado HubSpot</option>
                            <option value="hubspot_error" <?php selected($args['type'], 'hubspot_error'); ?>>Erro HubSpot</option>
                            <option value="hubspot_retry" <?php selected($args['type'], 'hubspot_retry'); ?>>Retry HubSpot</option>
                        </select>
                        <input type="date" name="date_from" value="<?php echo esc_attr($args['date_from']); ?>" placeholder="Data inicial">
                        <input type="date" name="date_to" value="<?php echo esc_attr($args['date_to']); ?>" placeholder="Data final">
                        <input type="text" name="s" value="<?php echo esc_attr($args['search']); ?>" placeholder="Buscar...">
                        <button type="submit" class="button">Filtrar</button>
                    </form>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th width="150">Tipo</th>
                            <th width="80">Lead ID</th>
                            <th>Mensagem</th>
                            <th width="120">IP</th>
                            <th width="150">Data</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7">Nenhum log encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html($log->id); ?></td>
                                    <td>
                                        <span class="clarke-badge <?php echo esc_attr(Clarke_Logger::get_type_badge_class($log->type)); ?>">
                                            <?php echo esc_html(Clarke_Logger::get_type_label($log->type)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log->lead_id): ?>
                                            <a href="<?php echo admin_url('admin.php?page=clarke-calculator-logs&lead_id=' . $log->lead_id); ?>">
                                                #<?php echo esc_html($log->lead_id); ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($log->message); ?></td>
                                    <td><code><?php echo esc_html($log->ip_address); ?></code></td>
                                    <td><?php echo esc_html(date('d/m/Y H:i:s', strtotime($log->created_at))); ?></td>
                                    <td>
                                        <button type="button" class="button button-small clarke-view-log" data-log-id="<?php echo esc_attr($log->id); ?>">
                                            Detalhes
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $args['page'],
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Log Details Modal -->
        <div id="clarke-log-modal" class="clarke-modal" style="display:none;">
            <div class="clarke-modal-content">
                <span class="clarke-modal-close">&times;</span>
                <h2>Detalhes do Log</h2>
                <div id="clarke-log-details"></div>
            </div>
        </div>
        <?php
        $this->render_page_footer();
    }

    /**
     * Render HubSpot settings page
     */
    public function render_hubspot_page() {
        $this->render_page_header('Configurações HubSpot');
        
        // Handle form submission - Connection settings
        if (isset($_POST['clarke_hubspot_submit']) && check_admin_referer('clarke_hubspot_settings')) {
            $access_token = sanitize_text_field($_POST['hubspot_access_token']);
            $enabled = isset($_POST['hubspot_enabled']) ? true : false;
            
            Clarke_HubSpot_Integration::save_settings($access_token, $enabled);
            
            // Clear properties cache when token changes
            Clarke_HubSpot_Integration::clear_properties_cache();
            
            echo '<div class="notice notice-success"><p>Configurações de conexão salvas com sucesso!</p></div>';
        }
        
        // Handle form submission - Property mapping
        if (isset($_POST['clarke_hubspot_mapping_submit']) && check_admin_referer('clarke_hubspot_mapping')) {
            $mapping = array();
            $calculator_fields = Clarke_HubSpot_Integration::get_calculator_fields();
            
            foreach (array_keys($calculator_fields) as $field) {
                $mapping[$field] = isset($_POST['mapping_' . $field]) ? sanitize_text_field($_POST['mapping_' . $field]) : '';
            }
            
            Clarke_HubSpot_Integration::save_property_mapping($mapping);
            
            echo '<div class="notice notice-success"><p>Mapeamento de propriedades salvo com sucesso!</p></div>';
        }
        
        $access_token = Clarke_HubSpot_Integration::get_access_token();
        $enabled = get_option('clarke_hubspot_enabled', false);
        $hubspot_properties = Clarke_HubSpot_Integration::get_hubspot_properties();
        $current_mapping = Clarke_HubSpot_Integration::get_property_mapping();
        $calculator_fields = Clarke_HubSpot_Integration::get_calculator_fields();
        
        ?>
        <div class="clarke-admin-content">
            <!-- Connection Settings -->
            <form method="post" action="">
                <?php wp_nonce_field('clarke_hubspot_settings'); ?>
                
                <div class="clarke-admin-card">
                    <h3>Conexão com HubSpot</h3>
                    <p class="description">Configure a integração direta com a API do HubSpot usando um Private App Token.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="hubspot_access_token">Token de Acesso</label>
                            </th>
                            <td>
                                <input type="password" id="hubspot_access_token" name="hubspot_access_token" value="<?php echo esc_attr($access_token); ?>" class="large-text" placeholder="pat-na1-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                <button type="button" id="clarke-test-connection" class="button" style="margin-top: 5px;">Testar Conexão</button>
                                <span id="clarke-connection-status"></span>
                                <p class="description">
                                    Token de acesso do Private App do HubSpot.<br>
                                    Encontre em: <strong>HubSpot → Desenvolvimento → Projetos → calculadoras → Distribuição</strong>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Ativar Integração</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="hubspot_enabled" value="1" <?php checked($enabled); ?>>
                                    Sincronizar leads automaticamente com HubSpot
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="clarke_hubspot_submit" class="button button-primary" value="Salvar Configurações">
                    </p>
                </div>
            </form>
            
            <!-- Property Mapping -->
            <?php if (!empty($access_token)): ?>
            <form method="post" action="">
                <?php wp_nonce_field('clarke_hubspot_mapping'); ?>
                
                <div class="clarke-admin-card">
                    <h3>
                        Mapeamento de Propriedades
                        <button type="button" id="clarke-refresh-properties" class="button button-small" style="margin-left: 10px;">
                            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Atualizar Propriedades
                        </button>
                    </h3>
                    <p class="description">Defina para qual propriedade do HubSpot cada campo da calculadora deve ser enviado.</p>
                    
                    <?php if (empty($hubspot_properties)): ?>
                        <p class="notice notice-warning" style="padding: 10px;">
                            <strong>Não foi possível carregar as propriedades do HubSpot.</strong><br>
                            Verifique se o Token de Acesso está correto e tem as permissões necessárias.
                        </p>
                    <?php else: ?>
                        <table class="form-table clarke-mapping-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Campo da Calculadora</th>
                                    <th style="width: 40%;">Propriedade do HubSpot</th>
                                    <th style="width: 30%;">Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calculator_fields as $field_key => $field_label): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($field_label); ?></strong>
                                        <code style="display: block; font-size: 11px; color: #666;"><?php echo esc_html($field_key); ?></code>
                                    </td>
                                    <td>
                                        <?php if ($field_key === 'email'): ?>
                                            <input type="text" value="email (obrigatório)" disabled class="regular-text" style="background: #f0f0f0;">
                                            <input type="hidden" name="mapping_email" value="email">
                                        <?php else: ?>
                                            <select name="mapping_<?php echo esc_attr($field_key); ?>" class="regular-text">
                                                <option value="">-- Não mapear --</option>
                                                <?php 
                                                $groups = array();
                                                foreach ($hubspot_properties as $prop) {
                                                    $groups[$prop['groupName']][] = $prop;
                                                }
                                                ksort($groups);
                                                
                                                foreach ($groups as $group_name => $props): 
                                                    $group_label = $group_name ?: 'Outros';
                                                ?>
                                                    <optgroup label="<?php echo esc_attr($group_label); ?>">
                                                        <?php foreach ($props as $prop): ?>
                                                            <option value="<?php echo esc_attr($prop['name']); ?>" <?php selected($current_mapping[$field_key] ?? '', $prop['name']); ?>>
                                                                <?php echo esc_html($prop['label']); ?> (<?php echo esc_html($prop['name']); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $descriptions = array(
                                            'email' => 'Email do lead (obrigatório)',
                                            'company_type' => 'Tipo de empresa selecionado',
                                            'company_size' => 'Porte da empresa',
                                            'monthly_expense' => 'Gasto mensal com energia',
                                            'recommended_strategy' => 'Estratégia recomendada pela calculadora',
                                            'scores' => 'Pontuações calculadas',
                                            'all_answers' => 'Todas as respostas do formulário',
                                            'lead_source' => 'Origem completa do lead (referrer + UTMs + página)',
                                            'page_url' => 'URL da página onde preencheu o formulário',
                                            'page_title' => 'Título da página do formulário',
                                            'utm_source' => 'Parâmetro utm_source',
                                            'utm_medium' => 'Parâmetro utm_medium',
                                            'utm_campaign' => 'Parâmetro utm_campaign',
                                            'utm_term' => 'Parâmetro utm_term',
                                            'utm_content' => 'Parâmetro utm_content',
                                        );
                                        echo esc_html($descriptions[$field_key] ?? '');
                                        ?>
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <p class="description" style="margin-top: 15px;">
                            <strong>Dica:</strong> Você pode criar propriedades personalizadas no HubSpot para armazenar dados específicos da calculadora. 
                            Vá em <strong>Configurações → Propriedades → Criar Propriedade</strong>.
                        </p>
                    <?php endif; ?>
                    
                    <p class="submit">
                        <input type="submit" name="clarke_hubspot_mapping_submit" class="button button-primary" value="Salvar Mapeamento">
                    </p>
                </div>
            </form>
            <?php else: ?>
                <div class="clarke-admin-card">
                    <h3>Mapeamento de Propriedades</h3>
                    <p class="notice notice-info" style="padding: 10px;">
                        Configure o Token de Acesso acima para ver as opções de mapeamento de propriedades.
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="clarke-admin-card">
                <h3>Instruções</h3>
                <ol>
                    <li>Copie o <strong>Token de Acesso</strong> da aba Distribuição do seu App no HubSpot</li>
                    <li>Cole no campo acima e clique em <strong>Testar Conexão</strong></li>
                    <li>Marque <strong>Ativar Integração</strong> e salve</li>
                    <li>Configure o <strong>Mapeamento de Propriedades</strong> para definir onde cada campo será salvo no HubSpot</li>
                </ol>
                <p><strong>Importante:</strong> O Token precisa ter os escopos: <code>crm.objects.contacts.read</code> e <code>crm.objects.contacts.write</code></p>
            </div>
        </div>
        <?php
        $this->render_page_footer();
    }

    /**
     * Render page header
     */
    private function render_page_header($title) {
        ?>
        <div class="wrap clarke-admin-wrap">
            <h1>
                <span class="dashicons dashicons-chart-area"></span>
                Clarke Calculator - <?php echo esc_html($title); ?>
            </h1>
            
            <nav class="clarke-admin-nav">
                <a href="<?php echo admin_url('admin.php?page=clarke-calculator'); ?>" class="<?php echo $this->is_current_page('clarke-calculator') ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-dashboard"></span> Dashboard
                </a>
                <a href="<?php echo admin_url('admin.php?page=clarke-calculator-leads'); ?>" class="<?php echo $this->is_current_page('clarke-calculator-leads') ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-groups"></span> Leads
                </a>
                <a href="<?php echo admin_url('admin.php?page=clarke-calculator-logs'); ?>" class="<?php echo $this->is_current_page('clarke-calculator-logs') ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-list-view"></span> Logs
                </a>
                <a href="<?php echo admin_url('admin.php?page=clarke-calculator-hubspot'); ?>" class="<?php echo $this->is_current_page('clarke-calculator-hubspot') ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-cloud"></span> HubSpot
                </a>
            </nav>
        <?php
    }

    /**
     * Render page footer
     */
    private function render_page_footer() {
        ?>
        </div>
        <?php
    }

    /**
     * Check if current page
     */
    private function is_current_page($page) {
        return isset($_GET['page']) && $_GET['page'] === $page;
    }

    /**
     * Render recent leads
     */
    private function render_recent_leads($limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clarke_calculator_leads';
        
        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
        
        if (empty($leads)) {
            echo '<p>Nenhum lead ainda.</p>';
            return;
        }
        
        echo '<ul class="clarke-recent-list">';
        foreach ($leads as $lead) {
            $status_class = !empty($lead->hubspot_contact_id) ? 'success' : 'warning';
            $status_text = !empty($lead->hubspot_contact_id) ? 'Sync' : 'Pendente';
            
            echo '<li>';
            echo '<span class="clarke-recent-email">' . esc_html($lead->email) . '</span>';
            echo '<span class="clarke-badge ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>';
            echo '<span class="clarke-recent-date">' . esc_html(human_time_diff(strtotime($lead->created_at))) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * Render recent logs
     */
    private function render_recent_logs($limit = 5) {
        $result = Clarke_Logger::get_logs(array('per_page' => $limit));
        $logs = $result['logs'];
        
        if (empty($logs)) {
            echo '<p>Nenhum log ainda.</p>';
            return;
        }
        
        echo '<ul class="clarke-recent-list">';
        foreach ($logs as $log) {
            echo '<li>';
            echo '<span class="clarke-badge ' . esc_attr(Clarke_Logger::get_type_badge_class($log->type)) . '">' . esc_html(Clarke_Logger::get_type_label($log->type)) . '</span>';
            echo '<span class="clarke-recent-message">' . esc_html(wp_trim_words($log->message, 8)) . '</span>';
            echo '<span class="clarke-recent-date">' . esc_html(human_time_diff(strtotime($log->created_at))) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * AJAX: Test HubSpot connection
     */
    public function ajax_test_hubspot() {
        check_ajax_referer('clarke_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão'));
        }

        $access_token = isset($_POST['access_token']) ? sanitize_text_field($_POST['access_token']) : '';
        
        $result = Clarke_HubSpot_Integration::test_connection($access_token);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX: Retry HubSpot sync
     */
    public function ajax_retry_hubspot() {
        check_ajax_referer('clarke_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão'));
        }

        $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;

        if (!$lead_id) {
            wp_send_json_error(array('message' => 'ID do lead inválido'));
        }

        $result = Clarke_HubSpot_Integration::retry_sync($lead_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX: Get log details
     */
    public function ajax_get_log_details() {
        check_ajax_referer('clarke_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão'));
        }

        $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;

        if (!$log_id) {
            wp_send_json_error(array('message' => 'ID do log inválido'));
        }

        $log = Clarke_Logger::get_log($log_id);

        if (!$log) {
            wp_send_json_error(array('message' => 'Log não encontrado'));
        }

        // Format log data
        $html = '<table class="clarke-log-detail-table">';
        $html .= '<tr><th>ID:</th><td>' . esc_html($log->id) . '</td></tr>';
        $html .= '<tr><th>Tipo:</th><td><span class="clarke-badge ' . esc_attr(Clarke_Logger::get_type_badge_class($log->type)) . '">' . esc_html(Clarke_Logger::get_type_label($log->type)) . '</span></td></tr>';
        $html .= '<tr><th>Lead ID:</th><td>' . ($log->lead_id ? esc_html($log->lead_id) : '-') . '</td></tr>';
        $html .= '<tr><th>Mensagem:</th><td>' . esc_html($log->message) . '</td></tr>';
        $html .= '<tr><th>IP:</th><td><code>' . esc_html($log->ip_address) . '</code></td></tr>';
        $html .= '<tr><th>User Agent:</th><td><small>' . esc_html($log->user_agent) . '</small></td></tr>';
        $html .= '<tr><th>Referrer:</th><td>' . esc_html($log->referrer) . '</td></tr>';
        $html .= '<tr><th>Data:</th><td>' . esc_html(date('d/m/Y H:i:s', strtotime($log->created_at))) . '</td></tr>';
        
        if (!empty($log->utm_source) || !empty($log->utm_medium) || !empty($log->utm_campaign)) {
            $html .= '<tr><th>UTMs:</th><td>';
            $html .= 'Source: ' . esc_html($log->utm_source) . '<br>';
            $html .= 'Medium: ' . esc_html($log->utm_medium) . '<br>';
            $html .= 'Campaign: ' . esc_html($log->utm_campaign);
            $html .= '</td></tr>';
        }
        
        if (!empty($log->request_data)) {
            $request = json_decode($log->request_data, true);
            $html .= '<tr><th>Request:</th><td><pre>' . esc_html(json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></td></tr>';
        }
        
        if (!empty($log->response_data)) {
            $response = json_decode($log->response_data, true);
            $html .= '<tr><th>Response:</th><td><pre>' . esc_html(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></td></tr>';
        }
        
        $html .= '</table>';

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX: Refresh HubSpot properties
     */
    public function ajax_refresh_properties() {
        check_ajax_referer('clarke_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sem permissão'));
        }

        // Clear cache and reload properties
        Clarke_HubSpot_Integration::clear_properties_cache();
        $properties = Clarke_HubSpot_Integration::get_hubspot_properties();

        if (empty($properties)) {
            wp_send_json_error(array(
                'message' => 'Não foi possível carregar as propriedades. Verifique o Token de Acesso.',
            ));
        }

        wp_send_json_success(array(
            'message' => count($properties) . ' propriedades carregadas com sucesso!',
            'count' => count($properties),
        ));
    }
}
