jQuery(function ($) {
    
    if (typeof PDADeactivate === 'undefined') {
        return;
    }

    let deactivateUrl = '';
    let pluginType = ''; // 'free' or 'pro'
    var slug = ''; // 'free' or 'pro'
    var pluginName = ''; // e.g. 'Prevent Direct Access' or 'Prevent Direct Access Gold'
    /* ===============================
       Capture deactivate click
       =============================== */

    $(document).on(
        'click',
        '.deactivate a[id^="deactivate-"]',
        function (e) {

            const id = this.id; // e.g. deactivate-prevent-direct-access-pro
            
            deactivateUrl = $(this).attr('href');

             if (id === 'deactivate-prevent-direct-access') {
                pluginType = 'free';
                slug = 'prevent-direct-access';
                pluginName = 'Prevent Direct Access';
            } else if (id === 'deactivate-prevent-direct-access-gold') {
                pluginType = 'pro';
                slug = 'prevent-direct-access-gold';
                pluginName = 'Prevent Direct Access Gold';
            } else {
                return; // Not our plugin
            }
            
            if (!this.id.includes(slug)) {
                return;
            }else{
                e.preventDefault();
            } 
            
            $('#pda-feedback-overlay .pda-plugin-name').text(pluginName);
            // Open modal (flex-safe)
            $('#pda-feedback-overlay').data('plugin', pluginType).addClass('is-open');
        }
    );

    /* ===============================
       Submit feedback
       =============================== */

    jQuery(document).ready(function ($) {

        $('.pda-submit').on('click', function (e) {
            e.preventDefault();

            const $btn = $(this);

            if ($btn.hasClass('loading')) {
                return;
            }

            $btn.addClass('loading');
            $btn.prop('disabled', true);
            $btn.find('.spinner').addClass('is-active');

            $btn.find('.pda-btn-text').hide();
            $btn.find('.pda-loader').show();

            var pluginType = $('#pda-feedback-overlay').data('plugin');

            $.ajax({
                url: PDADeactivate.ajax,
                type: 'POST',
                data: {
                    action: 'pda_store_deactivation_feedback',
                    nonce: PDADeactivate.nonce,
                    reason: $('input[name="reason"]:checked').val() || '',
                    not_working_reason: $('#not_working_reason').val() || '',
                    optional_detail: $('#optional_detail').val() || '',
                    pluginType: pluginType,
                    better_plugin_name : $('#better_plugin_name').val() || '',
                },
                success: function (response) {
                    if (response && response.success === true) {
                        // Continue deactivation
                        window.location.href = deactivateUrl;
                    } else {
                        resetButton($btn);
                    }
                },
                error: function () {
                    resetButton($btn);
                }
            });
        });

        function resetButton($btn) {
            $btn.removeClass('loading');
            $btn.prop('disabled', false);
            $btn.find('.pda-loader').hide();
            $btn.find('.pda-btn-text').show();
        }

    });


    /* ===============================
       Skip feedback
       =============================== */

    $('.pda-skip').on('click', function () {
        window.location.href = deactivateUrl;
    });

    /* ===============================
       Close modal interactions
       =============================== */

    // Close on outside click
    $('#pda-feedback-overlay').on('click', function () {
        $(this).removeClass('is-open');
    });

    // Prevent close when clicking inside modal
    $('#pda-feedback-modal').on('click', function (e) {
        e.stopPropagation();
    });

    // Close on ESC
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#pda-feedback-overlay').removeClass('is-open');
        }
    });

    /* ===============================
    Conditional extra field
    =============================== */

    $('input[name="reason"]').on('change', function () {

        // Hide all extra fields first
        $('.pda-reason-extra').hide('slow');

        // Show extra field only for "better_plugin"
        if ($(this).val() === 'not_working') {
            $(this)
                .closest('label')
                .next('.pda-reason-extra')
                .show('slow');
        }
    });


    $('input[name="reason"]').on('change', function () {

        // Hide all extra fields first
        $('.better_plugin_name').hide('slow');

        // Show extra field only for "better_plugin"
        if ($(this).val() === 'better_plugin') {
            $(this)
                .closest('label')
                .next('.better_plugin_name')
                .show('slow');
        }
    });

});
