/**
 * Clarke Calculator Admin JavaScript
 */

(function($) {
    'use strict';

    const ClarkeAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Test HubSpot connection
            $('#clarke-test-connection').on('click', this.testConnection.bind(this));

            // Retry HubSpot sync
            $(document).on('click', '.clarke-retry-hubspot', this.retryHubspot.bind(this));

            // View log details
            $(document).on('click', '.clarke-view-log', this.viewLogDetails.bind(this));

            // Close modal
            $(document).on('click', '.clarke-modal-close, .clarke-modal', this.closeModal.bind(this));
            $(document).on('click', '.clarke-modal-content', function(e) {
                e.stopPropagation();
            });

            // Refresh HubSpot properties
            $('#clarke-refresh-properties').on('click', this.refreshProperties.bind(this));

            // ESC key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('#clarke-log-modal').hide();
                }
            });
        },

        /**
         * Test HubSpot connection
         */
        testConnection: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            const $status = $('#clarke-connection-status');
            const accessToken = $('#hubspot_access_token').val();

            if (!accessToken) {
                $status.removeClass('success').addClass('error').text('Insira o Token de Acesso');
                return;
            }

            $button.prop('disabled', true).text('Testando...');
            $status.removeClass('success error').text('');

            $.ajax({
                url: clarkeAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_test_hubspot',
                    nonce: clarkeAdmin.nonce,
                    access_token: accessToken
                },
                success: function(response) {
                    if (response.success) {
                        $status.removeClass('error').addClass('success').text('✓ ' + response.data.message);
                    } else {
                        $status.removeClass('success').addClass('error').text('✗ ' + response.data.message);
                    }
                },
                error: function() {
                    $status.removeClass('success').addClass('error').text('✗ Erro de conexão');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Testar Conexão');
                }
            });
        },

        /**
         * Retry HubSpot sync
         */
        retryHubspot: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            const leadId = $button.data('lead-id');

            if (!leadId) return;

            $button.prop('disabled', true).text('Enviando...');

            $.ajax({
                url: clarkeAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_retry_hubspot',
                    nonce: clarkeAdmin.nonce,
                    lead_id: leadId
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('td').html('<span class="clarke-badge success">Sincronizado</span>');
                        alert('Lead sincronizado com sucesso!');
                    } else {
                        alert('Erro: ' + response.data.message);
                        $button.prop('disabled', false).text('Retry');
                    }
                },
                error: function() {
                    alert('Erro de conexão');
                    $button.prop('disabled', false).text('Retry');
                }
            });
        },

        /**
         * View log details
         */
        viewLogDetails: function(e) {
            e.preventDefault();

            const $button = $(e.target);
            const logId = $button.data('log-id');

            if (!logId) return;

            $button.prop('disabled', true);

            $.ajax({
                url: clarkeAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_get_log_details',
                    nonce: clarkeAdmin.nonce,
                    log_id: logId
                },
                success: function(response) {
                    if (response.success) {
                        $('#clarke-log-details').html(response.data.html);
                        $('#clarke-log-modal').show();
                    } else {
                        alert('Erro: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Erro ao carregar detalhes');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Close modal
         */
        closeModal: function(e) {
            if ($(e.target).hasClass('clarke-modal') || $(e.target).hasClass('clarke-modal-close')) {
                $('#clarke-log-modal').hide();
            }
        },

        /**
         * Refresh HubSpot properties
         */
        refreshProperties: function(e) {
            e.preventDefault();

            const $button = $(e.target).closest('button');
            $button.prop('disabled', true).find('.dashicons').addClass('spin');

            $.ajax({
                url: clarkeAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clarke_refresh_properties',
                    nonce: clarkeAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message + '. Recarregue a página para ver as propriedades atualizadas.');
                        location.reload();
                    } else {
                        alert('Erro: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Erro de conexão');
                },
                complete: function() {
                    $button.prop('disabled', false).find('.dashicons').removeClass('spin');
                }
            });
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        ClarkeAdmin.init();
    });

    // CSS for spinning icon
    $('<style>.dashicons.spin { animation: spin 1s linear infinite; } @keyframes spin { 100% { transform: rotate(360deg); } }</style>').appendTo('head');

})(jQuery);
