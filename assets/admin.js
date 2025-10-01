/**
 * Shiguang Post Admin Scripts
 * Author: fuhua
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    $(function() {

        const feedbackBox = $('#shiguang-action-feedback');

        $('.shiguang-id-list').on('click', '.create-draft-btn', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const postId = $btn.data('id');

            if ($btn.hasClass('loading')) {
                return; // 如果正在处理中，则不执行任何操作
            }

            // 设置按钮为加载状态
            $btn.addClass('loading').prop('disabled', true);
            $btn.text('创建中...');
            
            // 清空之前的反馈信息
            feedbackBox.hide().removeClass('notice-success notice-error');

            // 发起 AJAX 请求
            $.ajax({
                url: shiguang_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'shiguang_create_post',
                    id: postId,
                    nonce: shiguang_ajax_obj.nonce 
                },
                success: function(response) {
                    if (response.success) {
                        // 成功
                        feedbackBox.html('<p>' + response.data.message + '</p>').addClass('notice-success').show();
                        // 移除已使用的 ID 卡片
                        $('#id-card-' + postId).fadeOut(500, function() {
                            $(this).remove();
                            // 如果列表为空，显示提示信息
                            if ($('.shiguang-id-list .id-card').length === 0) {
                                $('.shiguang-id-list').html('<p>所有可用的ID都已使用完毕。</p>');
                            }
                        });
                    } else {
                        // 失败
                        feedbackBox.html('<p>' + response.data.message + '</p>').addClass('notice-error').show();
                        // 恢复按钮状态
                        $btn.removeClass('loading').prop('disabled', false);
                        $btn.text('创建草稿');
                    }
                },
                error: function() {
                    // AJAX 请求本身失败
                    feedbackBox.html('<p>请求失败，请检查网络或联系管理员。</p>').addClass('notice-error').show();
                    // 恢复按钮状态
                    $btn.removeClass('loading').prop('disabled', false);
                    $btn.text('创建草稿');
                }
            });
        });
    });

})(jQuery);
