/**
 * Clarke Energy Calculator JavaScript
 * Handles form navigation, validation, AJAX submission and results display
 */

(function($) {
    'use strict';

    // Calculator state
    const Calculator = {
        currentStep: 0,
        totalSteps: 15,
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
                referrer: document.referrer || ''
            };
            
            // Try to get from localStorage if not in URL (for returning visitors)
            if (!this.tracking.utm_source) {
                const stored = localStorage.getItem('clarke_tracking');
                if (stored) {
                    const storedData = JSON.parse(stored);
                    // Use stored data only if it's less than 30 days old
                    if (storedData.timestamp && (Date.now() - storedData.timestamp) < 30 * 24 * 60 * 60 * 1000) {
                        this.tracking = { ...storedData, referrer: document.referrer || storedData.referrer };
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
            $('#lead-email').on('input', function() {
                const isValid = self.isValidEmail($(this).val());
                $('.clarke-btn-submit').prop('disabled', !isValid);
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
            } else if (currentStepNum < 15) {
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

            if (!this.isValidEmail(email)) {
                alert('Por favor, informe um email válido.');
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
                        self.saveLead(email);
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
        saveLead: function(email) {
            const self = this;

            $.ajax({
                url: clarkeCalc.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_save_lead',
                    nonce: clarkeCalc.nonce,
                    email: email,
                    answers: this.answers,
                    scores: this.results.scores,
                    recommended_strategy: this.results.winner.key,
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
            $('#clarke-loading').removeClass('show');
            
            const html = this.buildResultsHTML();
            $('#clarke-results').html(html).addClass('show');
            
            this.scrollToTop();
        },

        // Build results HTML
        buildResultsHTML: function() {
            const winner = this.results.winner;
            const strategies = this.results.all_strategies;

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

            let tableRows = '';
            let count = 0;
            for (const key in strategies) {
                const strategy = strategies[key];
                if (count >= 4) break; // Show only top 4
                
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

                tableRows += `<tr class="${winnerClass} ${disqualifiedClass}">
                    <td>
                        <div class="strategy-name">
                            ${strategy.name}
                            ${winnerBadge}
                        </div>
                    </td>
                    <td>${economyText}</td>
                    <td>${monthlyText}</td>
                    <td>${paybackText}</td>
                    <td><div class="clarke-mini-stars">${miniStars}</div></td>
                </tr>`;
                count++;
            }

            // Add baseline (Mercado Cativo)
            tableRows += `<tr class="disqualified">
                <td>Mercado Cativo (atual)</td>
                <td>0%</td>
                <td>R$ 0</td>
                <td>-</td>
                <td><div class="clarke-mini-stars">
                    <svg class="clarke-mini-star empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div></td>
            </tr>`;

            return `
                <div class="clarke-results-header">
                    <span class="clarke-badge">Estratégia Recomendada</span>
                    <h2>Sua melhor opção é</h2>
                    <div class="clarke-strategy-name">${winner.name}</div>
                    <div class="clarke-stars">${starsHTML}</div>
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
                        Comparativo de Estratégias
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
                </div>

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

        // Validate email
        isValidEmail: function(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
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
