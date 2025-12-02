<?php
/**
 * Calculator Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="clarke-calculator" id="clarke-calculator">
    
    <!-- Progress Bar -->
    <div class="clarke-progress">
        <div class="clarke-progress-bar">
            <div class="clarke-progress-fill" style="width: 0%"></div>
        </div>
        <div class="clarke-progress-text">Pergunta <span class="current-step">1</span> de <span class="total-steps">15</span></div>
    </div>

    <!-- Form -->
    <form id="clarke-calculator-form">
        
        <!-- Step 0: Empresa ou Residência -->
        <div class="clarke-step active" data-step="0">
            <div class="clarke-question">
                <h3>Essa simulação é para sua empresa ou residência?</h3>
                <p>Selecione uma opção para continuar</p>
            </div>
            <div class="clarke-options clarke-options-grid">
                <label class="clarke-option">
                    <input type="radio" name="user_type" value="empresa" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Para minha empresa</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="user_type" value="residencia">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Para minha residência</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <div></div>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Residential Message -->
        <div class="clarke-residential-message" id="residential-message">
            <div class="clarke-residential-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3>Simulação apenas para empresas</h3>
            <p>Essa calculadora serve apenas para fazer uma simulação de cenários para empresas. Por enquanto ela não contempla simulação para residências. Em breve teremos novidades para você!</p>
            <div class="clarke-navigation" style="justify-content: center; border: none; padding-top: 30px;">
                <button type="button" class="clarke-btn clarke-btn-next" onclick="location.reload()">
                    Voltar ao início
                </button>
            </div>
        </div>

        <!-- Step 1: Tipo da Empresa -->
        <div class="clarke-step" data-step="1">
            <div class="clarke-question">
                <h3>Qual é o tipo da sua empresa?</h3>
                <p>Isso nos ajuda a entender seu perfil de consumo</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="industria" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Indústria</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="comercio">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Comércio</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="servicos">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Serviços</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="agronegocio">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Agronegócio</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="hospital_saude">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Hospital / Saúde</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="educacao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Educação</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Porte da Empresa -->
        <div class="clarke-step" data-step="2">
            <div class="clarke-question">
                <h3>Qual é o porte da sua empresa?</h3>
                <p>Baseado na sua demanda contratada</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="company_size" value="pequena" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Pequena (até 30 kW)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_size" value="media">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Média (30 a 500 kW)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_size" value="grande">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Grande (acima de 500 kW)</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Gasto Médio Mensal -->
        <div class="clarke-step" data-step="3">
            <div class="clarke-question">
                <h3>Qual seu gasto médio mensal com energia?</h3>
                <p>Valor aproximado da sua conta de luz</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="monthly_expense" value="ate_5000" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Até R$ 5.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_expense" value="5000_15000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>R$ 5.000 a R$ 15.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_expense" value="15000_50000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>R$ 15.000 a R$ 50.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_expense" value="acima_50000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Acima de R$ 50.000</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 4: Proposta Solar -->
        <div class="clarke-step" data-step="4">
            <div class="clarke-question">
                <h3>Já recebeu proposta para painéis solares?</h3>
            </div>
            <div class="clarke-options clarke-options-grid">
                <label class="clarke-option">
                    <input type="radio" name="solar_proposal" value="sim" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sim</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_proposal" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 5: Principal Preocupação -->
        <div class="clarke-step" data-step="5">
            <div class="clarke-question">
                <h3>Qual é sua principal preocupação com energia?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="custo_alto" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Custo alto da conta de luz</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="falta_previsibilidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Falta de previsibilidade nos custos</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="sustentabilidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sustentabilidade e meio ambiente</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="complexidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Complexidade de gestão</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 6: Período de Operação -->
        <div class="clarke-step" data-step="6">
            <div class="clarke-question">
                <h3>Qual é o principal período de operação da sua empresa?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="operation_period" value="dia" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Principalmente durante o dia</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="operation_period" value="noite">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Principalmente durante a noite</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="operation_period" value="24h">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Operamos 24 horas</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 7: Conhecimento ACL -->
        <div class="clarke-step" data-step="7">
            <div class="clarke-question">
                <h3>Qual seu conhecimento sobre o Mercado Livre de Energia?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="acl_knowledge" value="nunca_ouvi" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Nunca ouvi falar</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="acl_knowledge" value="ja_ouvi">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Já ouvi falar, mas não conheço bem</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="acl_knowledge" value="ja_recebi_proposta">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Já recebi proposta de migração</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 8: Contrato de Demanda -->
        <div class="clarke-step" data-step="8">
            <div class="clarke-question">
                <h3>Sua empresa possui contrato de demanda com a distribuidora?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="demand_contract" value="sim" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sim</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="demand_contract" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="demand_contract" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 9: Tarifa Atual -->
        <div class="clarke-step" data-step="9">
            <div class="clarke-question">
                <h3>Qual é sua tarifa atual de energia (R$/kWh)?</h3>
                <p>Você pode encontrar essa informação na sua conta de luz</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="menos_050" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Menos de R$ 0,50/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="050_070">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>R$ 0,50 a R$ 0,70/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="acima_070">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Acima de R$ 0,70/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 10: Espaço para Solar -->
        <div class="clarke-step" data-step="10">
            <div class="clarke-question">
                <h3>Sua empresa possui espaço disponível para instalação de painéis solares?</h3>
                <p>Telhado, estacionamento ou área livre</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="solar_space" value="sim" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sim, temos espaço disponível</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_space" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não, não temos espaço</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_space" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 11: Capacidade de Investimento -->
        <div class="clarke-step" data-step="11">
            <div class="clarke-question">
                <h3>Qual é a capacidade de investimento da sua empresa para soluções de energia?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="nada_opex" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não queremos investir (preferimos pagar mensalidade)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="ate_50000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Até R$ 50.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="50000_200000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>R$ 50.000 a R$ 200.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="acima_200000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Acima de R$ 200.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 12: Consumo Mensal -->
        <div class="clarke-step" data-step="12">
            <div class="clarke-question">
                <h3>Qual é o consumo mensal de energia da sua empresa (kWh)?</h3>
                <p>Você pode encontrar essa informação na sua conta de luz</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="ate_30000" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Até 30.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="30000_100000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>30.000 a 100.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="acima_100000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Acima de 100.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 13: Previsão de Crescimento -->
        <div class="clarke-step" data-step="13">
            <div class="clarke-question">
                <h3>Qual é a previsão de crescimento da sua empresa nos próximos 5 anos?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="significativo" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Crescimento significativo (acima de 30%)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="moderado">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Crescimento moderado (10% a 30%)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="estavel">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Manter estável</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 14: Acesso a Biomassa -->
        <div class="clarke-step" data-step="14">
            <div class="clarke-question">
                <h3>Sua empresa tem acesso a resíduos orgânicos ou biomassa?</h3>
                <p>Resíduos agrícolas, madeira, bagaço de cana, etc.</p>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="sim_grande_quantidade" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sim, geramos em grande quantidade</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="sim_fornecedores">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Sim, temos acesso a fornecedores</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="abertos_avaliar">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Estamos abertos a avaliar</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-check"></span>
                        <span>Não temos acesso</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 15: Email -->
        <div class="clarke-step clarke-email-step" data-step="15">
            <div class="clarke-question">
                <h3>Quase lá! Informe seu email para ver o resultado</h3>
                <p>Enviaremos uma cópia da análise para você</p>
            </div>
            <div class="clarke-email-form">
                <div class="clarke-input-group">
                    <input type="email" class="clarke-input" id="lead-email" name="email" placeholder="seu@email.com" required>
                </div>
                <button type="submit" class="clarke-btn clarke-btn-submit" style="width: 100%; justify-content: center;">
                    Ver meu resultado
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <p class="clarke-privacy-text">
                    Ao continuar, você concorda com nossa <a href="#">Política de Privacidade</a>
                </p>
            </div>
            <div class="clarke-navigation" style="border: none;">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </button>
            </div>
        </div>

    </form>

    <!-- Loading -->
    <div class="clarke-loading" id="clarke-loading">
        <div class="clarke-spinner"></div>
        <p>Analisando seu perfil...</p>
    </div>

    <!-- Results -->
    <div class="clarke-results" id="clarke-results">
        <!-- Content will be injected by JavaScript -->
    </div>

</div>
