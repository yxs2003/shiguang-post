/**
 * Shiguang Post Admin Scripts
 * Author: fuhua
 * Version: 1.3.1
 */
(function($) {
    'use strict';

    $(function() {
        const feedbackBox = $('#shiguang-action-feedback');
        const ajaxNonce = shiguang_ajax_obj.nonce;

        // Function to show feedback messages
        function showFeedback(message, type) {
            feedbackBox.html('<p>' + message + '</p>')
                .removeClass('notice-success notice-error')
                .addClass('notice-' + type)
                .slideDown();
        }

        // --- Feature 1: Create Draft ---
        $('#shiguang-id-list').on('click', '.create-draft-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $chip = $btn.closest('.id-chip');
            if ($btn.prop('disabled')) return;

            $btn.prop('disabled', true);
            $chip.css('opacity', 0.5);
            feedbackBox.slideUp();

            $.ajax({
                url: shiguang_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'shiguang_create_post',
                    id: $btn.data('id'),
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        showFeedback(response.data.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showFeedback(response.data.message, 'error');
                        $btn.prop('disabled', false);
                        $chip.css('opacity', 1);
                    }
                },
                error: function() {
                    showFeedback('请求失败，请检查网络或联系管理员。', 'error');
                    $btn.prop('disabled', false);
                    $chip.css('opacity', 1);
                }
            });
        });

        // --- Feature 2: Database Cleanup ---
        $('.cleanup-list').on('click', '.cleanup-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const action = $btn.data('action');
            const confirmMessage = $btn.data('confirm');

            if ($btn.prop('disabled')) return;
            
            // Use window.confirm as it's a standard browser feature.
            if (!window.confirm(confirmMessage)) {
                return;
            }
            
            $btn.prop('disabled', true).text('处理中...');
            feedbackBox.slideUp();

            $.ajax({
                url: shiguang_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'shiguang_cleanup_db',
                    cleanup_action: action,
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        showFeedback(response.data.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showFeedback(response.data.message, 'error');
                        $btn.prop('disabled', false).text('清理');
                    }
                },
                error: function() {
                    showFeedback('请求失败，请检查网络或联系管理员。', 'error');
                    $btn.prop('disabled', false).text('清理');
                }
            });
        });
    });
})(jQuery);

