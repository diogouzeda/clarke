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
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="user_type" value="empresa" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                        </span>
                        <span class="clarke-option-text">Para minha empresa</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="user_type" value="residencia">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </span>
                        <span class="clarke-option-text">Para minha residência</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <div></div>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Residential Message -->
        <div class="clarke-residential-message" id="residential-message">
            <div class="clarke-residential-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                        </span>
                        <span class="clarke-option-text">Indústria</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="comercio">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        </span>
                        <span class="clarke-option-text">Comércio</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="servicos">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                        </span>
                        <span class="clarke-option-text">Serviços</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="agronegocio">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M21 12a9 9 0 0 0-9-9v9h9z"/><path d="M12 12 8 8"/><path d="m8 8-4 4"/><path d="M12 12v8"/></svg>
                        </span>
                        <span class="clarke-option-text">Agronegócio</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="hospital_saude">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M9 16h6"/><path d="M12 13v6"/></svg>
                        </span>
                        <span class="clarke-option-text">Hospital / Saúde</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_type" value="educacao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </span>
                        <span class="clarke-option-text">Educação</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/></svg>
                        </span>
                        <span class="clarke-option-text">Pequena (até 30 kW)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_size" value="media">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="10" height="10" x="3" y="3" rx="1"/></svg>
                        </span>
                        <span class="clarke-option-text">Média (30 a 500 kW)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="company_size" value="grande">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="3" y="3" rx="1"/></svg>
                        </span>
                        <span class="clarke-option-text">Grande (acima de 500 kW)</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Gasto Médio Mensal -->
        <div class="clarke-step" data-step="3">
            <div class="clarke-question">
                <h3>Qual seu gasto médio mensal com energia?</h3>
                <p>Valor aproximado da sua conta de luz</p>
            </div>
            
            <!-- Range Slider Container -->
            <div class="clarke-range-container" id="expense-range-container">
                <div class="clarke-range-value-display">
                    <span class="clarke-range-currency">R$</span>
                    <span class="clarke-range-amount" id="expense-display">1.000</span>
                </div>
                <div class="clarke-range-wrapper">
                    <input type="range" 
                           id="expense-slider" 
                           class="clarke-range-slider" 
                           min="1000" 
                           max="50000" 
                           step="1000" 
                           value="1000">
                    <div class="clarke-range-track">
                        <div class="clarke-range-fill" id="expense-fill"></div>
                    </div>
                </div>
                <div class="clarke-range-labels">
                    <span>R$ 1.000</span>
                    <span>R$ 50.000+</span>
                </div>
            </div>
            
            <!-- Custom Value Input (hidden by default) -->
            <div class="clarke-custom-value-container" id="expense-custom-container" style="display: none;">
                <div class="clarke-custom-value-header">
                    <span class="clarke-custom-value-label">Digite o valor exato:</span>
                    <button type="button" class="clarke-custom-value-back" id="expense-back-to-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Voltar ao slider
                    </button>
                </div>
                <div class="clarke-custom-input-wrapper">
                    <span class="clarke-custom-input-prefix">R$</span>
                    <input type="text" 
                           id="expense-custom-input" 
                           class="clarke-custom-input" 
                           placeholder="0,00"
                           inputmode="numeric">
                </div>
            </div>
            
            <!-- Hidden input to store the actual value -->
            <input type="hidden" name="monthly_expense" id="monthly-expense-value" value="">
            
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 4: Proposta Solar -->
        <div class="clarke-step" data-step="4">
            <div class="clarke-question">
                <h3>Já recebeu proposta para painéis solares?</h3>
            </div>
            <div class="clarke-options">
                <label class="clarke-option">
                    <input type="radio" name="solar_proposal" value="sim" required>
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <span class="clarke-option-text">Sim</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_proposal" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </span>
                        <span class="clarke-option-text">Não</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="m13 13 6 6"/></svg>
                        </span>
                        <span class="clarke-option-text">Custo alto da conta de luz</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="falta_previsibilidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        </span>
                        <span class="clarke-option-text">Falta de previsibilidade nos custos</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="sustentabilidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22c1.25-.987 2.27-1.975 3.9-2.2a5.56 5.56 0 0 1 3.8 1.5 4 4 0 0 0 6.187-2.353 3.5 3.5 0 0 0 3.69-5.116A3.5 3.5 0 0 0 20.95 8 3.5 3.5 0 1 0 16 3.05a3.5 3.5 0 0 0-5.831 1.373 3.5 3.5 0 0 0-5.116 3.69 4 4 0 0 0-2.348 6.155C3.499 15.42 4.409 16.712 4.2 18.1 3.926 19.743 3.014 20.732 2 22"/><path d="M2 22 17 7"/></svg>
                        </span>
                        <span class="clarke-option-text">Sustentabilidade e meio ambiente</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="main_concern" value="complexidade">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        </span>
                        <span class="clarke-option-text">Complexidade de gestão</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        </span>
                        <span class="clarke-option-text">Principalmente durante o dia</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="operation_period" value="noite">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        </span>
                        <span class="clarke-option-text">Principalmente durante a noite</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="operation_period" value="24h">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <span class="clarke-option-text">Operamos 24 horas</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Nunca ouvi falar</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="acl_knowledge" value="ja_ouvi">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        <span class="clarke-option-text">Já ouvi falar, mas não conheço bem</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="acl_knowledge" value="ja_recebi_proposta">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                        </span>
                        <span class="clarke-option-text">Já recebi proposta de migração</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <span class="clarke-option-text">Sim</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="demand_contract" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </span>
                        <span class="clarke-option-text">Não</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="demand_contract" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
                        </span>
                        <span class="clarke-option-text">Menos de R$ 0,50/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="050_070">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/><path d="m9 10 2 2 4-4"/></svg>
                        </span>
                        <span class="clarke-option-text">R$ 0,50 a R$ 0,70/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="acima_070">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 17 2 2 4-4"/><path d="m3 7 2 2 4-4"/><path d="M13 6h8"/><path d="M13 12h8"/><path d="M13 18h8"/></svg>
                        </span>
                        <span class="clarke-option-text">Acima de R$ 0,70/kWh</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="current_tariff" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                        </span>
                        <span class="clarke-option-text">Sim, temos espaço disponível</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_space" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </span>
                        <span class="clarke-option-text">Não, não temos espaço</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="solar_space" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </span>
                        <span class="clarke-option-text">Não queremos investir (preferimos pagar mensalidade)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="ate_50000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2V5z"/><path d="M2 9v1c0 1.1.9 2 2 2h1"/><path d="M16 11h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Até R$ 50.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="50000_200000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </span>
                        <span class="clarke-option-text">R$ 50.000 a R$ 200.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="acima_200000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
                        </span>
                        <span class="clarke-option-text">Acima de R$ 200.000</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="investment_capacity" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </span>
                        <span class="clarke-option-text">Até 30.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="30000_100000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                        </span>
                        <span class="clarke-option-text">30.000 a 100.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="acima_100000">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>
                        </span>
                        <span class="clarke-option-text">Acima de 100.000 kWh/mês</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="monthly_consumption" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m2 20 7-7"/><path d="m2 12 5 5"/><path d="M12 4v16"/><path d="m22 4-10 10"/></svg>
                        </span>
                        <span class="clarke-option-text">Crescimento significativo (acima de 30%)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="moderado">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </span>
                        <span class="clarke-option-text">Crescimento moderado (10% a 30%)</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="estavel">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        </span>
                        <span class="clarke-option-text">Manter estável</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="growth_forecast" value="nao_sei">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="clarke-option-text">Não sei dizer</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>
                        </span>
                        <span class="clarke-option-text">Sim, geramos em grande quantidade</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="sim_fornecedores">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 16h6"/><path d="M21 12V7H5v14h8"/><path d="M12 7v14"/><path d="M3 7h18"/><path d="M3 11h10"/><path d="M3 15h10"/><path d="M16 19h6"/></svg>
                        </span>
                        <span class="clarke-option-text">Sim, temos acesso a fornecedores</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="abertos_avaliar">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </span>
                        <span class="clarke-option-text">Estamos abertos a avaliar</span>
                    </span>
                </label>
                <label class="clarke-option">
                    <input type="radio" name="biomass_access" value="nao">
                    <span class="clarke-option-label">
                        <span class="clarke-option-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </span>
                        <span class="clarke-option-text">Não temos acesso</span>
                    </span>
                </label>
            </div>
            <div class="clarke-navigation">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Voltar
                </button>
                <button type="button" class="clarke-btn clarke-btn-next" disabled>
                    Continuar
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <p class="clarke-privacy-text">
                    Ao continuar, você concorda com nossa <a href="#">Política de Privacidade</a>
                </p>
            </div>
            <div class="clarke-navigation" style="border: none;">
                <button type="button" class="clarke-btn clarke-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
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
