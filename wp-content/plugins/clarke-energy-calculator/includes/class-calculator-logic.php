<?php
/**
 * Calculator Logic Class
 * Handles all scoring, calculations and strategy determination
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clarke_Calculator_Logic {

    /**
     * Strategy definitions
     */
    private $strategies = array(
        'acl' => 'ACL (Mercado Livre de Energia)',
        'solar' => 'Solar (Energia Fotovoltaica)',
        'acl_solar' => 'ACL + Solar (Híbrido)',
        'biomassa' => 'Biomassa',
        'chp' => 'CHP (Gás) - Cogeração a Gás',
        'biomassa_acl' => 'Biomassa + ACL',
        'gd_compartilhada' => 'GD Compartilhada (Geração Distribuída)',
        'eficiencia' => 'Eficiência Energética'
    );

    /**
     * Economy ranges per strategy (min%, max%)
     */
    private $economy_ranges = array(
        'acl' => array(15, 35),
        'solar' => array(50, 90),
        'acl_solar' => array(60, 90),
        'biomassa' => array(40, 70),
        'chp' => array(25, 50),
        'biomassa_acl' => array(50, 75),
        'gd_compartilhada' => array(10, 20),
        'eficiencia' => array(5, 30)
    );

    /**
     * Payback periods per strategy
     */
    private $payback = array(
        'acl' => 'Imediato',
        'solar' => '3-7 anos',
        'acl_solar' => '2-6 anos',
        'biomassa' => '3-6 anos',
        'chp' => '2-6 anos',
        'biomassa_acl' => '3-5 anos',
        'gd_compartilhada' => 'N/A',
        'eficiencia' => '<2 anos'
    );

    /**
     * Scoring matrix for all questions
     */
    private $scoring_matrix = array(
        // Question 1: Tipo da Empresa
        'company_type' => array(
            'industria' => array('acl' => 15, 'solar' => 12, 'acl_solar' => 18, 'biomassa' => 20, 'chp' => 18, 'biomassa_acl' => 22, 'gd_compartilhada' => 8, 'eficiencia' => 12),
            'comercio' => array('acl' => 10, 'solar' => 15, 'acl_solar' => 12, 'biomassa' => 5, 'chp' => 8, 'biomassa_acl' => 6, 'gd_compartilhada' => 12, 'eficiencia' => 15),
            'servicos' => array('acl' => 12, 'solar' => 14, 'acl_solar' => 13, 'biomassa' => 3, 'chp' => 10, 'biomassa_acl' => 5, 'gd_compartilhada' => 14, 'eficiencia' => 15),
            'agronegocio' => array('acl' => 10, 'solar' => 18, 'acl_solar' => 16, 'biomassa' => 22, 'chp' => 8, 'biomassa_acl' => 24, 'gd_compartilhada' => 10, 'eficiencia' => 10),
            'hospital_saude' => array('acl' => 14, 'solar' => 10, 'acl_solar' => 15, 'biomassa' => 8, 'chp' => 20, 'biomassa_acl' => 12, 'gd_compartilhada' => 8, 'eficiencia' => 14),
            'educacao' => array('acl' => 8, 'solar' => 16, 'acl_solar' => 12, 'biomassa' => 5, 'chp' => 8, 'biomassa_acl' => 6, 'gd_compartilhada' => 15, 'eficiencia' => 16),
        ),
        // Question 2: Porte da Empresa
        'company_size' => array(
            'pequena' => array('acl' => 0, 'solar' => 15, 'acl_solar' => 0, 'biomassa' => 0, 'chp' => 0, 'biomassa_acl' => 0, 'gd_compartilhada' => 18, 'eficiencia' => 20),
            'media' => array('acl' => 12, 'solar' => 18, 'acl_solar' => 15, 'biomassa' => 10, 'chp' => 12, 'biomassa_acl' => 12, 'gd_compartilhada' => 16, 'eficiencia' => 18),
            'grande' => array('acl' => 20, 'solar' => 12, 'acl_solar' => 22, 'biomassa' => 18, 'chp' => 18, 'biomassa_acl' => 24, 'gd_compartilhada' => 8, 'eficiencia' => 15),
        ),
        // Question 3: Gasto Médio Mensal
        'monthly_expense' => array(
            'ate_5000' => array('acl' => 0, 'solar' => 12, 'acl_solar' => 0, 'biomassa' => 0, 'chp' => 0, 'biomassa_acl' => 0, 'gd_compartilhada' => 20, 'eficiencia' => 18),
            '5000_15000' => array('acl' => 8, 'solar' => 18, 'acl_solar' => 10, 'biomassa' => 5, 'chp' => 8, 'biomassa_acl' => 6, 'gd_compartilhada' => 16, 'eficiencia' => 16),
            '15000_50000' => array('acl' => 15, 'solar' => 16, 'acl_solar' => 18, 'biomassa' => 12, 'chp' => 14, 'biomassa_acl' => 16, 'gd_compartilhada' => 12, 'eficiencia' => 14),
            'acima_50000' => array('acl' => 22, 'solar' => 14, 'acl_solar' => 24, 'biomassa' => 20, 'chp' => 20, 'biomassa_acl' => 26, 'gd_compartilhada' => 8, 'eficiencia' => 12),
        ),
        // Question 4: Já recebeu proposta solar
        'solar_proposal' => array(
            'sim' => array('acl' => 5, 'solar' => 15, 'acl_solar' => 12, 'biomassa' => 3, 'chp' => 5, 'biomassa_acl' => 5, 'gd_compartilhada' => 8, 'eficiencia' => 8),
            'nao' => array('acl' => 8, 'solar' => 8, 'acl_solar' => 8, 'biomassa' => 8, 'chp' => 8, 'biomassa_acl' => 8, 'gd_compartilhada' => 8, 'eficiencia' => 10),
        ),
        // Question 5: Principal preocupação
        'main_concern' => array(
            'custo_alto' => array('acl' => 18, 'solar' => 12, 'acl_solar' => 20, 'biomassa' => 15, 'chp' => 14, 'biomassa_acl' => 22, 'gd_compartilhada' => 14, 'eficiencia' => 16),
            'falta_previsibilidade' => array('acl' => 20, 'solar' => 10, 'acl_solar' => 18, 'biomassa' => 12, 'chp' => 10, 'biomassa_acl' => 18, 'gd_compartilhada' => 12, 'eficiencia' => 8),
            'sustentabilidade' => array('acl' => 8, 'solar' => 22, 'acl_solar' => 18, 'biomassa' => 18, 'chp' => 10, 'biomassa_acl' => 20, 'gd_compartilhada' => 16, 'eficiencia' => 14),
            'complexidade' => array('acl' => 5, 'solar' => 15, 'acl_solar' => 8, 'biomassa' => 6, 'chp' => 8, 'biomassa_acl' => 6, 'gd_compartilhada' => 18, 'eficiencia' => 20),
        ),
        // Question 6: Período de operação
        'operation_period' => array(
            'dia' => array('acl' => 10, 'solar' => 22, 'acl_solar' => 18, 'biomassa' => 8, 'chp' => 10, 'biomassa_acl' => 12, 'gd_compartilhada' => 20, 'eficiencia' => 12),
            'noite' => array('acl' => 16, 'solar' => 5, 'acl_solar' => 12, 'biomassa' => 14, 'chp' => 16, 'biomassa_acl' => 18, 'gd_compartilhada' => 8, 'eficiencia' => 14),
            '24h' => array('acl' => 18, 'solar' => 10, 'acl_solar' => 16, 'biomassa' => 18, 'chp' => 20, 'biomassa_acl' => 22, 'gd_compartilhada' => 10, 'eficiencia' => 16),
        ),
        // Question 7: Conhecimento sobre Mercado Livre
        'acl_knowledge' => array(
            'nunca_ouvi' => array('acl' => 0, 'solar' => 12, 'acl_solar' => 0, 'biomassa' => 8, 'chp' => 8, 'biomassa_acl' => 0, 'gd_compartilhada' => 14, 'eficiencia' => 12),
            'ja_ouvi' => array('acl' => 12, 'solar' => 10, 'acl_solar' => 10, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 10, 'eficiencia' => 10),
            'ja_recebi_proposta' => array('acl' => 20, 'solar' => 8, 'acl_solar' => 18, 'biomassa' => 10, 'chp' => 12, 'biomassa_acl' => 18, 'gd_compartilhada' => 8, 'eficiencia' => 8),
        ),
        // Question 8: Contrato de Demanda
        'demand_contract' => array(
            'sim' => array('acl' => 18, 'solar' => 10, 'acl_solar' => 16, 'biomassa' => 14, 'chp' => 16, 'biomassa_acl' => 18, 'gd_compartilhada' => 8, 'eficiencia' => 12),
            'nao' => array('acl' => 8, 'solar' => 14, 'acl_solar' => 10, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 16, 'eficiencia' => 16),
            'nao_sei' => array('acl' => 5, 'solar' => 12, 'acl_solar' => 8, 'biomassa' => 8, 'chp' => 8, 'biomassa_acl' => 8, 'gd_compartilhada' => 14, 'eficiencia' => 14),
        ),
        // Question 9: Tarifa Atual
        'current_tariff' => array(
            'menos_050' => array('acl' => 8, 'solar' => 12, 'acl_solar' => 10, 'biomassa' => 8, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 12, 'eficiencia' => 14),
            '050_070' => array('acl' => 16, 'solar' => 14, 'acl_solar' => 16, 'biomassa' => 12, 'chp' => 14, 'biomassa_acl' => 16, 'gd_compartilhada' => 14, 'eficiencia' => 14),
            'acima_070' => array('acl' => 22, 'solar' => 18, 'acl_solar' => 24, 'biomassa' => 18, 'chp' => 18, 'biomassa_acl' => 24, 'gd_compartilhada' => 16, 'eficiencia' => 12),
            'nao_sei' => array('acl' => 10, 'solar' => 10, 'acl_solar' => 10, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 12, 'eficiencia' => 16),
        ),
        // Question 10: Espaço para painéis (peso 1.3x)
        'solar_space' => array(
            'sim' => array('acl' => 8, 'solar' => 25, 'acl_solar' => 22, 'biomassa' => 6, 'chp' => 8, 'biomassa_acl' => 12, 'gd_compartilhada' => 14, 'eficiencia' => 10),
            'nao' => array('acl' => 15, 'solar' => 0, 'acl_solar' => 0, 'biomassa' => 12, 'chp' => 14, 'biomassa_acl' => 14, 'gd_compartilhada' => 18, 'eficiencia' => 16),
            'nao_sei' => array('acl' => 10, 'solar' => 8, 'acl_solar' => 8, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 12, 'eficiencia' => 12),
        ),
        // Question 11: Capacidade de investimento (peso 1.2x)
        'investment_capacity' => array(
            'nada_opex' => array('acl' => 20, 'solar' => 0, 'acl_solar' => 0, 'biomassa' => 0, 'chp' => 0, 'biomassa_acl' => 0, 'gd_compartilhada' => 22, 'eficiencia' => 18),
            'ate_50000' => array('acl' => 12, 'solar' => 16, 'acl_solar' => 10, 'biomassa' => 5, 'chp' => 6, 'biomassa_acl' => 6, 'gd_compartilhada' => 18, 'eficiencia' => 20),
            '50000_200000' => array('acl' => 14, 'solar' => 20, 'acl_solar' => 18, 'biomassa' => 12, 'chp' => 14, 'biomassa_acl' => 14, 'gd_compartilhada' => 14, 'eficiencia' => 16),
            'acima_200000' => array('acl' => 16, 'solar' => 18, 'acl_solar' => 24, 'biomassa' => 22, 'chp' => 22, 'biomassa_acl' => 26, 'gd_compartilhada' => 10, 'eficiencia' => 12),
            'nao_sei' => array('acl' => 14, 'solar' => 12, 'acl_solar' => 14, 'biomassa' => 12, 'chp' => 12, 'biomassa_acl' => 14, 'gd_compartilhada' => 14, 'eficiencia' => 14),
        ),
        // Question 12: Consumo mensal (peso 1.3x) - usado para critérios eliminatórios
        'monthly_consumption' => array(
            'ate_30000' => array('acl' => 0, 'solar' => 14, 'acl_solar' => 0, 'biomassa' => 8, 'chp' => 0, 'biomassa_acl' => 0, 'gd_compartilhada' => 18, 'eficiencia' => 16),
            '30000_100000' => array('acl' => 16, 'solar' => 16, 'acl_solar' => 18, 'biomassa' => 14, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 14, 'eficiencia' => 14),
            'acima_100000' => array('acl' => 22, 'solar' => 14, 'acl_solar' => 24, 'biomassa' => 20, 'chp' => 22, 'biomassa_acl' => 26, 'gd_compartilhada' => 8, 'eficiencia' => 12),
            'nao_sei' => array('acl' => 10, 'solar' => 12, 'acl_solar' => 12, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 10, 'gd_compartilhada' => 14, 'eficiencia' => 14),
        ),
        // Question 13: Previsão de crescimento
        'growth_forecast' => array(
            'significativo' => array('acl' => 16, 'solar' => 14, 'acl_solar' => 20, 'biomassa' => 16, 'chp' => 16, 'biomassa_acl' => 22, 'gd_compartilhada' => 12, 'eficiencia' => 10),
            'moderado' => array('acl' => 14, 'solar' => 16, 'acl_solar' => 18, 'biomassa' => 14, 'chp' => 14, 'biomassa_acl' => 18, 'gd_compartilhada' => 14, 'eficiencia' => 12),
            'estavel' => array('acl' => 16, 'solar' => 18, 'acl_solar' => 16, 'biomassa' => 14, 'chp' => 14, 'biomassa_acl' => 16, 'gd_compartilhada' => 16, 'eficiencia' => 16),
            'nao_sei' => array('acl' => 10, 'solar' => 12, 'acl_solar' => 12, 'biomassa' => 10, 'chp' => 10, 'biomassa_acl' => 12, 'gd_compartilhada' => 12, 'eficiencia' => 14),
        ),
        // Question 14: Acesso a biomassa (peso 1.4x)
        'biomass_access' => array(
            'sim_grande_quantidade' => array('acl' => 8, 'solar' => 8, 'acl_solar' => 12, 'biomassa' => 30, 'chp' => 10, 'biomassa_acl' => 32, 'gd_compartilhada' => 6, 'eficiencia' => 10),
            'sim_fornecedores' => array('acl' => 10, 'solar' => 10, 'acl_solar' => 12, 'biomassa' => 24, 'chp' => 12, 'biomassa_acl' => 26, 'gd_compartilhada' => 8, 'eficiencia' => 10),
            'abertos_avaliar' => array('acl' => 12, 'solar' => 12, 'acl_solar' => 14, 'biomassa' => 16, 'chp' => 12, 'biomassa_acl' => 18, 'gd_compartilhada' => 10, 'eficiencia' => 12),
            'nao' => array('acl' => 14, 'solar' => 14, 'acl_solar' => 14, 'biomassa' => 0, 'chp' => 14, 'biomassa_acl' => 0, 'gd_compartilhada' => 14, 'eficiencia' => 14),
        ),
    );

    /**
     * Weight multipliers for specific questions
     */
    private $weight_multipliers = array(
        'solar_space' => 1.3,
        'investment_capacity' => 1.2,
        'monthly_consumption' => 1.3,
        'biomass_access' => 1.4,
    );

    /**
     * Convert numeric expense value to category
     */
    private function convert_expense_to_category($value) {
        $value = intval($value);
        
        if ($value <= 5000) {
            return 'ate_5000';
        } elseif ($value <= 15000) {
            return '5000_15000';
        } elseif ($value <= 50000) {
            return '15000_50000';
        } else {
            return 'acima_50000';
        }
    }

    /**
     * Calculate scores for all strategies
     */
    public function calculate_scores($answers) {
        $scores = array(
            'acl' => 0,
            'solar' => 0,
            'acl_solar' => 0,
            'biomassa' => 0,
            'chp' => 0,
            'biomassa_acl' => 0,
            'gd_compartilhada' => 0,
            'eficiencia' => 0,
        );

        // Convert numeric monthly_expense to category if it's a number
        if (isset($answers['monthly_expense']) && is_numeric($answers['monthly_expense'])) {
            $answers['monthly_expense'] = $this->convert_expense_to_category($answers['monthly_expense']);
        }

        // Calculate base scores
        foreach ($answers as $question => $answer) {
            if (isset($this->scoring_matrix[$question][$answer])) {
                $question_scores = $this->scoring_matrix[$question][$answer];
                $multiplier = isset($this->weight_multipliers[$question]) ? $this->weight_multipliers[$question] : 1;
                
                foreach ($question_scores as $strategy => $points) {
                    $scores[$strategy] += $points * $multiplier;
                }
            }
        }

        // Apply disqualification criteria
        $scores = $this->apply_disqualification($scores, $answers);

        return $scores;
    }

    /**
     * Apply disqualification criteria
     */
    private function apply_disqualification($scores, $answers) {
        // ACL disqualification
        if (isset($answers['monthly_consumption']) && $answers['monthly_consumption'] === 'ate_30000') {
            $scores['acl'] = 0;
        }
        if (isset($answers['monthly_expense']) && $answers['monthly_expense'] === 'ate_5000') {
            $scores['acl'] = 0;
        }

        // Solar disqualification
        if (isset($answers['solar_space']) && $answers['solar_space'] === 'nao') {
            $scores['solar'] = 0;
        }
        if (isset($answers['investment_capacity']) && $answers['investment_capacity'] === 'nada_opex') {
            $scores['solar'] = 0;
        }

        // ACL + Solar disqualification
        if (isset($answers['monthly_consumption']) && $answers['monthly_consumption'] === 'ate_30000') {
            $scores['acl_solar'] = 0;
        }
        if (isset($answers['solar_space']) && $answers['solar_space'] === 'nao') {
            $scores['acl_solar'] = 0;
        }
        if (isset($answers['investment_capacity']) && $answers['investment_capacity'] === 'nada_opex') {
            $scores['acl_solar'] = 0;
        }

        // Biomassa disqualification
        if (isset($answers['biomass_access']) && $answers['biomass_access'] === 'nao') {
            $scores['biomassa'] = 0;
        }
        if (isset($answers['investment_capacity']) && in_array($answers['investment_capacity'], array('nada_opex', 'ate_50000'))) {
            $scores['biomassa'] = 0;
        }

        // CHP disqualification
        if (isset($answers['investment_capacity']) && in_array($answers['investment_capacity'], array('nada_opex', 'ate_50000'))) {
            $scores['chp'] = 0;
        }
        if (isset($answers['monthly_consumption']) && in_array($answers['monthly_consumption'], array('ate_30000', '30000_100000'))) {
            $scores['chp'] = 0;
        }

        // Biomassa + ACL disqualification
        if (isset($answers['biomass_access']) && $answers['biomass_access'] === 'nao') {
            $scores['biomassa_acl'] = 0;
        }
        if (isset($answers['monthly_consumption']) && in_array($answers['monthly_consumption'], array('ate_30000', '30000_100000'))) {
            $scores['biomassa_acl'] = 0;
        }
        if (isset($answers['investment_capacity']) && !in_array($answers['investment_capacity'], array('acima_200000'))) {
            $scores['biomassa_acl'] = 0;
        }

        return $scores;
    }

    /**
     * Determine winning strategy
     */
    public function get_winning_strategy($scores) {
        // Priority order for tiebreaker
        $priority = array('acl_solar', 'biomassa_acl', 'solar', 'acl', 'chp', 'biomassa', 'gd_compartilhada', 'eficiencia');
        
        $max_score = max($scores);
        
        if ($max_score === 0) {
            // All strategies disqualified, return eficiencia as fallback
            return 'eficiencia';
        }

        // Find strategies with max score
        $winners = array_keys($scores, $max_score);

        // If tie, use priority order
        if (count($winners) > 1) {
            foreach ($priority as $strategy) {
                if (in_array($strategy, $winners)) {
                    return $strategy;
                }
            }
        }

        return $winners[0];
    }

    /**
     * Get strategy name
     */
    public function get_strategy_name($key) {
        return isset($this->strategies[$key]) ? $this->strategies[$key] : $key;
    }

    /**
     * Get all strategies
     */
    public function get_all_strategies() {
        return $this->strategies;
    }

    /**
     * Calculate economy estimates
     */
    public function calculate_economy($strategy, $monthly_expense) {
        // Parse monthly expense
        $expense_values = array(
            'ate_5000' => 3500,
            '5000_15000' => 10000,
            '15000_50000' => 32500,
            'acima_50000' => 75000,
        );

        $expense = isset($expense_values[$monthly_expense]) ? $expense_values[$monthly_expense] : 20000;
        $range = $this->economy_ranges[$strategy];

        return array(
            'percentage_min' => $range[0],
            'percentage_max' => $range[1],
            'monthly_min' => round($expense * ($range[0] / 100)),
            'monthly_max' => round($expense * ($range[1] / 100)),
            'yearly_min' => round($expense * ($range[0] / 100) * 12),
            'yearly_max' => round($expense * ($range[1] / 100) * 12),
        );
    }

    /**
     * Get payback period
     */
    public function get_payback($strategy) {
        return isset($this->payback[$strategy]) ? $this->payback[$strategy] : 'N/A';
    }

    /**
     * Get investment estimate
     */
    public function get_investment_estimate($strategy, $monthly_consumption) {
        // Parse consumption
        $consumption_values = array(
            'ate_30000' => 20000,
            '30000_100000' => 65000,
            'acima_100000' => 150000,
            'nao_sei' => 50000,
        );

        $consumption = isset($consumption_values[$monthly_consumption]) ? $consumption_values[$monthly_consumption] : 50000;

        switch ($strategy) {
            case 'acl':
            case 'gd_compartilhada':
                return array('type' => 'opex', 'value' => 'R$ 0 (modelo OPEX)');
            
            case 'solar':
            case 'acl_solar':
                // Potência necessária (kWp) = (Consumo * 0.7) / 150
                $power_kwp = ($consumption * 0.7) / 150;
                $investment = $power_kwp * 4500;
                return array('type' => 'capex', 'value' => 'R$ ' . number_format($investment, 0, ',', '.'));
            
            case 'biomassa':
                return array('type' => 'capex', 'value' => 'R$ 800.000 a R$ 2.000.000');
            
            case 'chp':
                return array('type' => 'capex', 'value' => 'R$ 1.000.000 a R$ 3.000.000');
            
            case 'biomassa_acl':
                return array('type' => 'capex', 'value' => 'R$ 1.200.000 a R$ 3.500.000');
            
            case 'eficiencia':
                return array('type' => 'varies', 'value' => 'Variável (geralmente baixo)');
            
            default:
                return array('type' => 'unknown', 'value' => 'A definir');
        }
    }

    /**
     * Calculate adequacy stars (0-5)
     */
    public function calculate_stars($score, $max_possible = 300) {
        $percentage = ($score / $max_possible) * 100;
        
        if ($percentage >= 81) return 5;
        if ($percentage >= 61) return 4;
        if ($percentage >= 41) return 3;
        if ($percentage >= 21) return 2;
        return 1;
    }

    /**
     * Generate reasons for recommendation
     */
    public function get_recommendation_reasons($strategy, $answers) {
        $reasons = array();

        switch ($strategy) {
            case 'acl':
                $reasons[] = 'Seu consumo de energia permite acesso ao Mercado Livre';
                if (isset($answers['main_concern']) && $answers['main_concern'] === 'falta_previsibilidade') {
                    $reasons[] = 'O ACL oferece maior previsibilidade de custos';
                }
                if (isset($answers['investment_capacity']) && $answers['investment_capacity'] === 'nada_opex') {
                    $reasons[] = 'Não requer investimento inicial (modelo OPEX)';
                }
                break;

            case 'solar':
                if (isset($answers['solar_space']) && $answers['solar_space'] === 'sim') {
                    $reasons[] = 'Você possui espaço disponível para instalação';
                }
                if (isset($answers['operation_period']) && $answers['operation_period'] === 'dia') {
                    $reasons[] = 'Sua operação diurna maximiza o aproveitamento solar';
                }
                if (isset($answers['main_concern']) && $answers['main_concern'] === 'sustentabilidade') {
                    $reasons[] = 'Energia 100% limpa e renovável';
                }
                break;

            case 'acl_solar':
                $reasons[] = 'Combina os benefícios do Mercado Livre com geração própria';
                $reasons[] = 'Maior economia combinando as duas estratégias';
                if (isset($answers['growth_forecast']) && $answers['growth_forecast'] === 'significativo') {
                    $reasons[] = 'Solução escalável para acompanhar seu crescimento';
                }
                break;

            case 'biomassa':
                if (isset($answers['biomass_access']) && in_array($answers['biomass_access'], array('sim_grande_quantidade', 'sim_fornecedores'))) {
                    $reasons[] = 'Você tem acesso a matéria-prima de biomassa';
                }
                if (isset($answers['company_type']) && in_array($answers['company_type'], array('industria', 'agronegocio'))) {
                    $reasons[] = 'Seu segmento tem sinergia natural com biomassa';
                }
                $reasons[] = 'Fonte de energia renovável e sustentável';
                break;

            case 'chp':
                if (isset($answers['operation_period']) && $answers['operation_period'] === '24h') {
                    $reasons[] = 'Ideal para operação contínua 24 horas';
                }
                if (isset($answers['company_type']) && $answers['company_type'] === 'hospital_saude') {
                    $reasons[] = 'Perfeito para demandas térmicas e elétricas simultâneas';
                }
                $reasons[] = 'Alta eficiência energética na cogeração';
                break;

            case 'biomassa_acl':
                $reasons[] = 'Maximiza economia com geração própria + mercado livre';
                if (isset($answers['monthly_expense']) && $answers['monthly_expense'] === 'acima_50000') {
                    $reasons[] = 'Seu alto consumo justifica o investimento';
                }
                $reasons[] = 'Modelo híbrido com flexibilidade operacional';
                break;

            case 'gd_compartilhada':
                $reasons[] = 'Não requer investimento inicial';
                $reasons[] = 'Economia sem necessidade de espaço próprio';
                if (isset($answers['main_concern']) && $answers['main_concern'] === 'complexidade') {
                    $reasons[] = 'Baixa complexidade de implementação';
                }
                break;

            case 'eficiencia':
                $reasons[] = 'Sempre aplicável independente do porte';
                $reasons[] = 'Retorno rápido sobre o investimento';
                $reasons[] = 'Pode ser combinada com outras estratégias';
                break;
        }

        return array_slice($reasons, 0, 3); // Return max 3 reasons
    }

    /**
     * Get all results data
     */
    public function get_full_results($answers) {
        $scores = $this->calculate_scores($answers);
        $winner = $this->get_winning_strategy($scores);
        $monthly_expense = isset($answers['monthly_expense']) ? $answers['monthly_expense'] : '15000_50000';
        $monthly_consumption = isset($answers['monthly_consumption']) ? $answers['monthly_consumption'] : 'nao_sei';
        
        // Sort scores descending
        arsort($scores);
        
        $results = array(
            'winner' => array(
                'key' => $winner,
                'name' => $this->get_strategy_name($winner),
                'score' => $scores[$winner],
                'stars' => $this->calculate_stars($scores[$winner]),
                'economy' => $this->calculate_economy($winner, $monthly_expense),
                'payback' => $this->get_payback($winner),
                'investment' => $this->get_investment_estimate($winner, $monthly_consumption),
                'reasons' => $this->get_recommendation_reasons($winner, $answers),
            ),
            'all_strategies' => array(),
            'scores' => $scores,
        );

        // Build comparison table
        foreach ($scores as $strategy => $score) {
            $results['all_strategies'][$strategy] = array(
                'key' => $strategy,
                'name' => $this->get_strategy_name($strategy),
                'score' => $score,
                'stars' => $this->calculate_stars($score),
                'economy' => $this->calculate_economy($strategy, $monthly_expense),
                'payback' => $this->get_payback($strategy),
                'investment' => $this->get_investment_estimate($strategy, $monthly_consumption),
                'is_winner' => $strategy === $winner,
                'is_disqualified' => $score === 0,
            );
        }

        return $results;
    }
}
