/**
 * Clarke Energy Calculator JavaScript
 * Handles form navigation, validation, AJAX submission and results display
 */

(function($) {
    'use strict';

    // Calculator state
    const Calculator = {
        currentStep: 0,
        totalSteps: 14,
        answers: {},
        results: null,
        tracking: {},

        // Initialize
        init: function() {
            this.captureTracking();
            this.bindEvents();
            this.updateProgress();
            this.initRangeSlider();
        },

        // Capture UTM parameters and referrer
        captureTracking: function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            this.tracking = {
                utm_source: urlParams.get('utm_source') || '',
                utm_medium: urlParams.get('utm_medium') || '',
                utm_campaign: urlParams.get('utm_campaign') || '',
                utm_term: urlParams.get('utm_term') || '',
                utm_content: urlParams.get('utm_content') || '',
                referrer: document.referrer || '',
                page_url: window.location.href || '',
                page_title: document.title || '',
                lead_source: this.getHubSpotSource()
            };
            
            // Try to get from localStorage if not in URL (for returning visitors)
            if (!this.tracking.utm_source) {
                const stored = localStorage.getItem('clarke_tracking');
                if (stored) {
                    const storedData = JSON.parse(stored);
                    // Use stored data only if it's less than 30 days old
                    if (storedData.timestamp && (Date.now() - storedData.timestamp) < 30 * 24 * 60 * 60 * 1000) {
                        this.tracking = { 
                            ...storedData, 
                            referrer: document.referrer || storedData.referrer,
                            page_url: window.location.href,
                            page_title: document.title,
                            lead_source: this.getHubSpotSource() || storedData.lead_source
                        };
                    }
                }
            } else {
                // Save to localStorage for future visits
                localStorage.setItem('clarke_tracking', JSON.stringify({
                    ...this.tracking,
                    timestamp: Date.now()
                }));
            }
        },

        // Get HubSpot tracking source from cookies
        getHubSpotSource: function() {
            // HubSpot stores tracking data in __hstc cookie
            // Format: <hub_id>.<visitor_id>.<first_visit_timestamp>.<previous_visit_timestamp>.<current_visit_timestamp>.<session_count>
            const hstc = this.getCookie('__hstc');
            const hssc = this.getCookie('__hssc');
            const hubspotutk = this.getCookie('hubspotutk');
            
            // Try to get the original source from __hssrc (source data)
            let source = '';
            
            // Check for referrer-based source
            const referrer = document.referrer;
            if (referrer) {
                try {
                    const refUrl = new URL(referrer);
                    const refHost = refUrl.hostname.toLowerCase();
                    
                    // Detect source from referrer
                    if (refHost.includes('google')) {
                        source = 'Google';
                    } else if (refHost.includes('facebook') || refHost.includes('fb.com')) {
                        source = 'Facebook';
                    } else if (refHost.includes('instagram')) {
                        source = 'Instagram';
                    } else if (refHost.includes('linkedin')) {
                        source = 'LinkedIn';
                    } else if (refHost.includes('twitter') || refHost.includes('x.com')) {
                        source = 'Twitter/X';
                    } else if (refHost.includes('youtube')) {
                        source = 'YouTube';
                    } else if (refHost.includes('bing')) {
                        source = 'Bing';
                    } else if (refHost.includes('yahoo')) {
                        source = 'Yahoo';
                    } else if (!refHost.includes(window.location.hostname)) {
                        source = refHost;
                    }
                } catch (e) {
                    // Invalid URL, ignore
                }
            }
            
            // If we have UTM source, use that instead
            const urlParams = new URLSearchParams(window.location.search);
            const utmSource = urlParams.get('utm_source');
            if (utmSource) {
                source = utmSource;
                
                // Add medium if available
                const utmMedium = urlParams.get('utm_medium');
                if (utmMedium) {
                    source += ' / ' + utmMedium;
                }
                
                // Add campaign if available
                const utmCampaign = urlParams.get('utm_campaign');
                if (utmCampaign) {
                    source += ' / ' + utmCampaign;
                }
            }
            
            // If no source detected, mark as direct or organic
            if (!source) {
                if (referrer && !referrer.includes(window.location.hostname)) {
                    source = 'Referral: ' + referrer;
                } else if (!referrer) {
                    source = 'Acesso Direto';
                } else {
                    source = 'Navegação Interna';
                }
            }
            
            // Add page info
            source += ' | Página: ' + (document.title || window.location.pathname);
            
            return source;
        },

        // Helper to get cookie value
        getCookie: function(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return '';
        },

        // Bind event listeners
        bindEvents: function() {
            const self = this;

            // Radio button change
            $(document).on('change', '.clarke-calculator input[type="radio"]', function() {
                self.handleOptionChange($(this));
            });

            // Next button click
            $(document).on('click', '.clarke-btn-next', function() {
                self.nextStep();
            });

            // Back button click
            $(document).on('click', '.clarke-btn-back', function() {
                self.prevStep();
            });

            // Form submit
            $('#clarke-calculator-form').on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });

            // Email input validation
            $('#lead-email').on('input blur', function() {
                const email = $(this).val();
                const isValid = self.isValidEmail(email);
                const $error = $('#email-error');
                
                if (email && !isValid) {
                    $error.show();
                    $(this).addClass('invalid');
                } else {
                    $error.hide();
                    $(this).removeClass('invalid');
                }
                
                // Update submit button state
                const name = $('#lead-name').val().trim();
                $('.clarke-btn-submit').prop('disabled', !isValid || !name);
            });

            // Name input validation
            $('#lead-name').on('input', function() {
                const name = $(this).val().trim();
                const email = $('#lead-email').val();
                const isValidEmail = self.isValidEmail(email);
                
                // Update submit button state
                $('.clarke-btn-submit').prop('disabled', !name || !isValidEmail);
            });
        },

        // Initialize Range Slider for expense
        initRangeSlider: function() {
            const self = this;
            const $slider = $('#expense-slider');
            const $display = $('#expense-display');
            const $fill = $('#expense-fill');
            const $rangeContainer = $('#expense-range-container');
            const $customContainer = $('#expense-custom-container');
            const $customInput = $('#expense-custom-input');
            const $hiddenInput = $('#monthly-expense-value');
            const $backToSlider = $('#expense-back-to-slider');
            const $step3 = $('.clarke-step[data-step="3"]');

            // Update slider display and fill
            function updateSlider(value) {
                const min = parseInt($slider.attr('min'));
                const max = parseInt($slider.attr('max'));
                const percentage = ((value - min) / (max - min)) * 100;
                
                $fill.css('width', percentage + '%');
                $display.text(self.formatCurrency(value));
                $hiddenInput.val(value);
                self.answers['monthly_expense'] = value;
                
                // Enable next button
                $step3.find('.clarke-btn-next').prop('disabled', false);
            }

            // Slider input event
            $slider.on('input change', function() {
                const value = parseInt($(this).val());
                updateSlider(value);
                
                // If reached max (50000), show custom input
                if (value >= 50000) {
                    setTimeout(function() {
                        self.showCustomExpenseInput();
                    }, 300);
                }
            });

            // Also enable on click/touch
            $slider.on('mousedown touchstart', function() {
                const value = parseInt($(this).val());
                updateSlider(value);
            });

            // Back to slider button
            $backToSlider.on('click', function() {
                self.showExpenseSlider();
            });

            // Custom input with currency mask
            $customInput.on('input', function() {
                let value = $(this).val();
                
                // Remove non-numeric characters
                value = value.replace(/\D/g, '');
                
                // Convert to number and format
                if (value) {
                    const numValue = parseInt(value);
                    $(this).val(self.formatCurrencyInput(numValue));
                    $hiddenInput.val(numValue);
                    self.answers['monthly_expense'] = numValue;
                    $step3.find('.clarke-btn-next').prop('disabled', false);
                } else {
                    $hiddenInput.val('');
                    $step3.find('.clarke-btn-next').prop('disabled', true);
                }
            });

            // Focus handling for custom input
            $customInput.on('focus', function() {
                if ($(this).val() === '') {
                    $(this).val('');
                }
            });

            // Initialize with default value
            updateSlider(1000);
            $step3.find('.clarke-btn-next').prop('disabled', true); // Start disabled until user interacts
        },

        // Show custom expense input
        showCustomExpenseInput: function() {
            $('#expense-range-container').slideUp(300);
            $('#expense-custom-container').slideDown(300, function() {
                $('#expense-custom-input').focus();
            });
            // Pre-fill with 50000
            $('#expense-custom-input').val(this.formatCurrencyInput(50000));
            $('#monthly-expense-value').val(50000);
            this.answers['monthly_expense'] = 50000;
        },

        // Show expense slider
        showExpenseSlider: function() {
            $('#expense-custom-container').slideUp(300);
            $('#expense-range-container').slideDown(300);
            $('#expense-slider').val(25000).trigger('input');
        },

        // Format currency for display (1.000)
        formatCurrency: function(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        // Format currency for input (1.000,00)
        formatCurrencyInput: function(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',00';
        },

        // Handle option selection
        handleOptionChange: function($input) {
            const name = $input.attr('name');
            const value = $input.val();

            // Store answer
            this.answers[name] = value;

            // Enable next button
            const $step = $input.closest('.clarke-step');
            $step.find('.clarke-btn-next').prop('disabled', false);

            // Check for residential user
            if (name === 'user_type' && value === 'residencia') {
                this.showResidentialMessage();
                return;
            }
        },

        // Show residential message
        showResidentialMessage: function() {
            $('.clarke-step').removeClass('active');
            $('.clarke-progress').hide();
            $('#residential-message').addClass('show');
        },

        // Go to next step
        nextStep: function() {
            const $currentStep = $('.clarke-step.active');
            const currentStepNum = parseInt($currentStep.data('step'));

            // Validate current step
            // Step 3 uses range slider, not radio buttons
            if (currentStepNum === 3) {
                const expenseValue = $('#monthly-expense-value').val();
                if (!expenseValue || expenseValue === '') {
                    return;
                }
            } else if (currentStepNum < 14) {
                const $selectedOption = $currentStep.find('input[type="radio"]:checked');
                if ($selectedOption.length === 0) {
                    return;
                }
            }

            // Move to next step
            if (currentStepNum < this.totalSteps) {
                $currentStep.removeClass('active');
                $(`.clarke-step[data-step="${currentStepNum + 1}"]`).addClass('active');
                this.currentStep = currentStepNum + 1;
                this.updateProgress();
                
                // Scroll to top of calculator
                this.scrollToTop();
            }
        },

        // Go to previous step
        prevStep: function() {
            const $currentStep = $('.clarke-step.active');
            const currentStepNum = parseInt($currentStep.data('step'));

            if (currentStepNum > 0) {
                $currentStep.removeClass('active');
                $(`.clarke-step[data-step="${currentStepNum - 1}"]`).addClass('active');
                this.currentStep = currentStepNum - 1;
                this.updateProgress();
                
                // Scroll to top of calculator
                this.scrollToTop();
            }
        },

        // Update progress bar
        updateProgress: function() {
            const progress = Math.round((this.currentStep / this.totalSteps) * 100);
            $('.clarke-progress-fill').css('width', `${progress}%`);
            $('.current-step').text(Math.max(1, this.currentStep));
            $('.total-steps').text(this.totalSteps);
            
            // Animate percentage counter
            const $percent = $('.progress-percent');
            const currentPercent = parseInt($percent.text()) || 0;
            this.animateValue($percent, currentPercent, progress, 400);
        },

        // Animate numeric value
        animateValue: function($element, start, end, duration) {
            const range = end - start;
            const startTime = performance.now();
            
            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (range * easeOut));
                
                $element.text(current);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };
            
            requestAnimationFrame(animate);
        },

        // Submit form
        submitForm: function() {
            const self = this;
            const email = $('#lead-email').val();
            const name = $('#lead-name').val().trim();

            if (!name) {
                alert('Por favor, informe seu nome.');
                return;
            }

            if (!this.isValidEmail(email)) {
                $('#email-error').show();
                $('#lead-email').addClass('invalid');
                return;
            }

            // Show loading
            $('.clarke-step').removeClass('active');
            $('.clarke-progress').hide();
            $('#clarke-loading').addClass('show');

            // First calculate results
            $.ajax({
                url: clarkeCalc.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_calculate',
                    nonce: clarkeCalc.nonce,
                    answers: this.answers
                },
                success: function(response) {
                    if (response.success) {
                        self.results = response.data;

                        // Save lead
                        self.saveLead(name, email);
                    } else {
                        self.showError(response.data.message || 'Erro ao calcular. Tente novamente.');
                    }
                },
                error: function() {
                    self.showError('Erro de conexão. Verifique sua internet e tente novamente.');
                }
            });
        },

        // Save lead to database
        saveLead: function(name, email) {
            const self = this;

            $.ajax({
                url: clarkeCalc.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_save_lead',
                    nonce: clarkeCalc.nonce,
                    name: name,
                    email: email,
                    answers: this.answers,
                    scores: this.results.scores,
                    recommended_strategy: this.results.winner.key,
                    lead_source: this.tracking.lead_source,
                    page_url: this.tracking.page_url,
                    page_title: this.tracking.page_title,
                    utm_source: this.tracking.utm_source,
                    utm_medium: this.tracking.utm_medium,
                    utm_campaign: this.tracking.utm_campaign,
                    utm_term: this.tracking.utm_term,
                    utm_content: this.tracking.utm_content,
                    referrer: this.tracking.referrer
                },
                success: function() {
                    // Show results regardless of lead save status
                    self.showResults();
                },
                error: function() {
                    // Show results even if lead save fails
                    self.showResults();
                }
            });
        },

        // Show results
        showResults: function() {
            const self = this;
            $('#clarke-loading').removeClass('show');
            
            const html = this.buildResultsHTML();
            $('#clarke-results').html(html).addClass('show');
            
            // Bind analysis form submission
            $('#btn-request-analysis').on('click', function() {
                self.requestAnalysis();
            });
            
            this.scrollToTop();
        },

        // Request analysis for ACL strategies
        requestAnalysis: function() {
            const self = this;
            const phone = $('#analysis-phone').val().trim();
            
            if (!phone) {
                alert('Por favor, informe seu telefone para contato.');
                return;
            }
            
            // Phone validation (Brazilian format)
            const phoneRegex = /^[\d\s\(\)\-\+]{10,}$/;
            if (!phoneRegex.test(phone)) {
                alert('Por favor, informe um telefone válido.');
                return;
            }
            
            const $btn = $('#btn-request-analysis');
            const originalText = $btn.html();
            $btn.html('Enviando...').prop('disabled', true);
            
            $.ajax({
                url: clarkeCalc.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_request_analysis',
                    nonce: clarkeCalc.nonce,
                    phone: phone,
                    strategy: this.results.winner.key,
                    email: $('#lead-email').val(),
                    name: $('#lead-name').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#analysis-form').html(`
                            <div style="text-align: center; padding: 20px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: white; margin-bottom: 12px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <p style="color: white; font-weight: 600; font-size: 16px; margin: 0;">Solicitação enviada com sucesso!</p>
                                <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 8px 0 0 0;">Entraremos em contato em breve.</p>
                            </div>
                        `);
                    } else {
                        alert(response.data.message || 'Erro ao enviar solicitação. Tente novamente.');
                        $btn.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Erro de conexão. Tente novamente.');
                    $btn.html(originalText).prop('disabled', false);
                }
            });
        },

        // Build results HTML
        buildResultsHTML: function() {
            const winner = this.results.winner;
            const strategies = this.results.all_strategies;

            // Strategy descriptions
            const strategyDescriptions = {
                'acl': 'O Mercado Livre de Energia (ACL) permite que sua empresa compre energia diretamente de geradores ou comercializadoras, negociando preços e condições. É ideal para empresas com consumo a partir de 500 kW de demanda contratada.',
                'solar': 'A energia solar fotovoltaica gera eletricidade através de painéis solares instalados em sua propriedade. Oferece economia de longo prazo e contribui para a sustentabilidade ambiental.',
                'acl_solar': 'Combina os benefícios do Mercado Livre de Energia com a geração solar própria. Essa estratégia híbrida maximiza a economia e oferece maior segurança energética.',
                'biomassa': 'Utiliza resíduos orgânicos (como bagaço de cana, madeira ou dejetos) para gerar energia. É uma solução sustentável especialmente vantajosa para empresas do agronegócio ou indústrias com acesso a biomassa.',
                'chp': 'A Cogeração a Gás (CHP) produz energia elétrica e térmica simultaneamente a partir de gás natural. Ideal para operações que necessitam de calor industrial, como hospitais e indústrias.',
                'biomassa_acl': 'Integra a geração própria com biomassa à compra de energia no Mercado Livre. Oferece flexibilidade e aproveita recursos orgânicos disponíveis na empresa.',
                'gd_compartilhada': 'A Geração Distribuída Compartilhada permite receber créditos de energia gerada em usinas remotas (principalmente solares). Não requer investimento em infraestrutura própria.',
                'eficiencia': 'Foca na otimização do consumo através de equipamentos mais eficientes, automação e gestão inteligente de energia. É o primeiro passo para qualquer estratégia de economia.'
            };

            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                const starClass = i <= winner.stars ? '' : 'empty';
                starsHTML += `<svg class="clarke-star ${starClass}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>`;
            }

            let reasonsHTML = '';
            winner.reasons.forEach(reason => {
                reasonsHTML += `<li>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>${reason}</span>
                </li>`;
            });

            // Get winner description
            const winnerDescription = strategyDescriptions[winner.key] || '';

            // Build table rows for ALL strategies
            let tableRows = '';
            for (const key in strategies) {
                const strategy = strategies[key];
                
                const winnerClass = strategy.is_winner ? 'winner' : '';
                const disqualifiedClass = strategy.is_disqualified ? 'disqualified' : '';
                const winnerBadge = strategy.is_winner ? '<span class="winner-badge">Recomendada</span>' : '';

                let miniStars = '';
                for (let i = 1; i <= 5; i++) {
                    const starClass = i <= strategy.stars ? '' : 'empty';
                    miniStars += `<svg class="clarke-mini-star ${starClass}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>`;
                }

                const economyText = strategy.is_disqualified ? '-' : `${strategy.economy.percentage_min}% - ${strategy.economy.percentage_max}%`;
                const monthlyText = strategy.is_disqualified ? '-' : `R$ ${this.formatNumber(strategy.economy.monthly_min)} - R$ ${this.formatNumber(strategy.economy.monthly_max)}`;
                const paybackText = strategy.is_disqualified ? '-' : strategy.payback;

                // Add description tooltip
                const description = strategyDescriptions[key] || '';
                const descriptionAttr = description ? `data-description="${description.replace(/"/g, '&quot;')}"` : '';

                tableRows += `<tr class="${winnerClass} ${disqualifiedClass}" ${descriptionAttr}>
                    <td>
                        <div class="strategy-name">
                            ${strategy.name}
                            ${winnerBadge}
                        </div>
                        <div class="strategy-description-mini">${description ? description.substring(0, 100) + '...' : ''}</div>
                    </td>
                    <td>${economyText}</td>
                    <td>${monthlyText}</td>
                    <td>${paybackText}</td>
                    <td><div class="clarke-mini-stars">${miniStars}</div></td>
                </tr>`;
            }

            // Add baseline (Mercado Cativo)
            tableRows += `<tr class="disqualified baseline">
                <td>
                    <div class="strategy-name">Mercado Cativo (atual)</div>
                    <div class="strategy-description-mini">Permanecer comprando energia da distribuidora local no modelo regulado tradicional.</div>
                </td>
                <td>0%</td>
                <td>R$ 0</td>
                <td>-</td>
                <td><div class="clarke-mini-stars">
                    <svg class="clarke-mini-star empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div></td>
            </tr>`;

            // Check if winner is ACL-related (show analysis form)
            const aclStrategies = ['acl', 'acl_solar', 'biomassa_acl'];
            const showAnalysisForm = aclStrategies.includes(winner.key);
            
            let ctaSection = '';
            if (showAnalysisForm) {
                ctaSection = `
                <div class="clarke-analysis-cta">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Solicite uma Análise Detalhada
                    </h3>
                    <p>A estratégia <strong>${winner.name}</strong> requer uma análise técnica do seu perfil de consumo. Nossa equipe especializada pode avaliar sua elegibilidade e apresentar uma proposta personalizada com os valores exatos de economia.</p>
                    <div class="clarke-analysis-form" id="analysis-form">
                        <div class="clarke-analysis-form-row">
                            <input type="text" class="clarke-input" id="analysis-phone" placeholder="Telefone para contato" />
                            <button type="button" class="clarke-btn clarke-btn-analysis" id="btn-request-analysis">
                                Solicitar Análise Gratuita
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        </div>
                        <p class="clarke-analysis-note">Um especialista entrará em contato em até 24 horas úteis.</p>
                    </div>
                </div>`;
            } else {
                ctaSection = `
                <div class="clarke-next-steps">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        Próximos Passos
                    </h3>
                    <p>Com base no seu perfil, identificamos que a estratégia <strong>${winner.name}</strong> é a mais adequada para sua empresa. Para avançar, nossa equipe pode fazer uma análise técnica detalhada e apresentar uma proposta personalizada.</p>
                    <a href="#" class="clarke-cta-btn">
                        Falar com um especialista
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>
                </div>`;
            }

            return `
                <div class="clarke-results-header">
                    <span class="clarke-badge">Estratégia Recomendada</span>
                    <h2>Sua melhor opção é</h2>
                    <div class="clarke-strategy-name">${winner.name}</div>
                    <div class="clarke-stars">${starsHTML}</div>
                </div>

                <div class="clarke-strategy-description">
                    <p>${winnerDescription}</p>
                </div>

                <div class="clarke-economy-section">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Economia Estimada
                    </h3>
                    <div class="clarke-economy-cards">
                        <div class="clarke-economy-card">
                            <div class="clarke-economy-label">Percentual</div>
                            <div class="clarke-economy-value">${winner.economy.percentage_min}% - ${winner.economy.percentage_max}%</div>
                        </div>
                        <div class="clarke-economy-card highlight">
                            <div class="clarke-economy-label">Por mês</div>
                            <div class="clarke-economy-value">R$ ${this.formatNumber(winner.economy.monthly_min)} - R$ ${this.formatNumber(winner.economy.monthly_max)}</div>
                        </div>
                        <div class="clarke-economy-card">
                            <div class="clarke-economy-label">Por ano</div>
                            <div class="clarke-economy-value">R$ ${this.formatNumber(winner.economy.yearly_min)} - R$ ${this.formatNumber(winner.economy.yearly_max)}</div>
                        </div>
                    </div>
                </div>

                <div class="clarke-economy-section">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Retorno do Investimento
                    </h3>
                    <div class="clarke-economy-cards">
                        <div class="clarke-economy-card">
                            <div class="clarke-economy-label">Payback</div>
                            <div class="clarke-economy-value">${winner.payback}</div>
                        </div>
                        <div class="clarke-economy-card">
                            <div class="clarke-economy-label">Investimento Inicial</div>
                            <div class="clarke-economy-value" style="font-size: 18px;">${winner.investment.value}</div>
                        </div>
                    </div>
                </div>

                <div class="clarke-reasons">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Por que essa estratégia?
                    </h3>
                    <ul class="clarke-reasons-list">
                        ${reasonsHTML}
                    </ul>
                </div>

                <div class="clarke-comparison">
                    <h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Comparativo de Todas as Estratégias
                    </h3>
                    <div class="clarke-table-wrapper">
                        <table class="clarke-table">
                            <thead>
                                <tr>
                                    <th>Estratégia</th>
                                    <th>Economia</th>
                                    <th>Por mês</th>
                                    <th>Payback</th>
                                    <th>Adequação</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>

                ${ctaSection}

                <div class="clarke-disclaimer">
                    <strong>Importante:</strong> Os valores apresentados são estimativas baseadas nas informações fornecidas e médias de mercado. Os resultados reais podem variar de acordo com fatores específicos do seu caso. Recomendamos um estudo técnico detalhado para uma análise precisa.
                </div>

                <div class="clarke-restart">
                    <button type="button" class="clarke-restart-btn" onclick="location.reload()">
                        Fazer nova simulação
                    </button>
                </div>
            `;
        },

        // Format number with dots
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        // Validate email with comprehensive regex
        isValidEmail: function(email) {
            if (!email) return false;
            
            // More comprehensive email regex
            const regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
            
            if (!regex.test(email)) return false;
            
            // Check for common invalid patterns
            const invalidPatterns = [
                /test@test/i,
                /fake@/i,
                /asdf@/i,
                /abc@abc/i,
                /@test\.com$/i,
                /@example\.com$/i,
                /@localhost/i,
                /^a+@/i,
                /^\d+@\d+\./
            ];
            
            for (const pattern of invalidPatterns) {
                if (pattern.test(email)) return false;
            }
            
            // Check minimum length for domain
            const parts = email.split('@');
            if (parts.length !== 2) return false;
            if (parts[0].length < 1 || parts[1].length < 4) return false;
            
            return true;
        },

        // Show error
        showError: function(message) {
            $('#clarke-loading').removeClass('show');
            alert(message);
            location.reload();
        },

        // Scroll to top of calculator
        scrollToTop: function() {
            const $calculator = $('#clarke-calculator');
            if ($calculator.length) {
                $('html, body').animate({
                    scrollTop: $calculator.offset().top - 50
                }, 300);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('#clarke-calculator').length) {
            Calculator.init();
        }
    });

})(jQuery);
