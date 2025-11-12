<?php
/**
 * Plugin Name:       Shiguang Post
 * Plugin URI:        https://www.shiguang.ink/2079
 * Description:       扫描并使用 WordPress 中空缺的文章 ID 来发布文章。
 * Version:           1.3.1
 * Author:            fuhua
 * Author URI:        https://github.com/yxs2003/shiguang-post
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shiguang-post
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SGP_Shiguang_Post' ) ) {

    class SGP_Shiguang_Post {

        public function __construct() {
            add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
            add_action( 'wp_ajax_shiguang_create_post', [ $this, 'ajax_create_post' ] );
            add_action( 'wp_ajax_shiguang_cleanup_db', [ $this, 'ajax_cleanup_db' ] );
        }

        public function add_admin_menu() {
            add_management_page('时光机文章与优化', '时光机文章', 'manage_options', 'shiguang-post', [ $this, 'render_options_page' ] );
        }

        public function render_options_page() {
            global $wpdb;
            $revisions_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
            $autodrafts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'");
            $trashed_posts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'");
            $unused_ids = $this->get_unused_ids();
            $total_posts = wp_count_posts()->publish;
            ?>
            <div class="wrap shiguang-post-wrap">
                <h1>
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <?php _e( '时光机文章与数据库优化', 'shiguang-post' ); ?>
                </h1>
                
                <div id="shiguang-action-feedback" class="notice" style="display:none;"></div>

                <div class="shiguang-dashboard">
                    <div class="shiguang-main-content">
                        <div class="shiguang-card">
                            <div class="card-header">
                                <h3><?php _e( '数据库优化', 'shiguang-post' ); ?></h3>
                                <p><?php _e( '清理数据库中的冗余数据，提升网站运行效率。', 'shiguang-post' ); ?></p>
                            </div>
                            <div class="card-body">
                                <div class="notice notice-alt notice-warning">
                                    <p><strong><?php _e( '重要提示：', 'shiguang-post' ); ?></strong> <?php _e( '清理前请务必备份数据库，操作不可逆！', 'shiguang-post' ); ?></p>
                                </div>
                                <ul class="cleanup-list">
                                    <li>
                                        <div class="cleanup-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-rotate-ccw"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                            <div class="cleanup-info">
                                                <strong><?php _e( '文章修订版本', 'shiguang-post' ); ?></strong>
                                                <span><?php printf( __( '%s 个项目', 'shiguang-post' ), number_format_i18n( $revisions_count ) ); ?></span>
                                            </div>
                                        </div>
                                        <button class="button button-danger cleanup-btn" data-action="revisions" data-confirm="<?php esc_attr_e('确定要删除所有文章修订版本吗？', 'shiguang-post'); ?>" <?php disabled( $revisions_count, 0 ); ?>><?php _e( '清理', 'shiguang-post' ); ?></button>
                                    </li>
                                    <li>
                                        <div class="cleanup-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                            <div class="cleanup-info">
                                                <strong><?php _e( '自动草稿', 'shiguang-post' ); ?></strong>
                                                <span><?php printf( __( '%s 个项目', 'shiguang-post' ), number_format_i18n( $autodrafts_count ) ); ?></span>
                                            </div>
                                        </div>
                                        <button class="button button-danger cleanup-btn" data-action="autodrafts" data-confirm="<?php esc_attr_e('确定要删除所有自动草稿吗？', 'shiguang-post'); ?>" <?php disabled( $autodrafts_count, 0 ); ?>><?php _e( '清理', 'shiguang-post' ); ?></button>
                                    </li>
                                    <li>
                                        <div class="cleanup-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            <div class="cleanup-info">
                                                <strong><?php _e( '回收站', 'shiguang-post' ); ?></strong>
                                                <span><?php printf( __( '%s 个项目', 'shiguang-post' ), number_format_i18n( $trashed_posts_count ) ); ?></span>
                                            </div>
                                        </div>
                                        <button class="button button-danger cleanup-btn" data-action="trashed" data-confirm="<?php esc_attr_e('确定要永久清空回收站吗？', 'shiguang-post'); ?>" <?php disabled( $trashed_posts_count, 0 ); ?>><?php _e( '清理', 'shiguang-post' ); ?></button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="shiguang-card">
                             <div class="card-header">
                                <h3><?php _e( '时光机文章', 'shiguang-post' ); ?></h3>
                                <p><?php _e( '使用数据库中被跳过的ID来创建新文章。', 'shiguang-post' ); ?></p>
                            </div>
                            <div class="card-body">
                                <div id="shiguang-id-list" class="shiguang-id-list">
                                    <?php if ( ! empty( $unused_ids ) ): ?>
                                        <?php foreach ( $unused_ids as $id ): ?>
                                            <div class="id-chip" id="id-card-<?php echo esc_attr( $id ); ?>">
                                                <span>ID: <?php echo esc_html( $id ); ?></span>
                                                <button class="create-draft-btn" data-id="<?php echo esc_attr( $id ); ?>" title="<?php esc_attr_e('使用此ID创建草稿', 'shiguang-post'); ?>">+</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="no-ids-found"><?php _e( '太棒了！数据库中没有发现任何空缺的ID。', 'shiguang-post' ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="shiguang-sidebar">
                        <div class="shiguang-card">
                            <div class="card-header">
                                <h4><?php _e( '状态总览', 'shiguang-post' ); ?></h4>
                            </div>
                            <div class="card-body">
                                <ul class="overview-list">
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                        <strong><?php _e( '已发布文章', 'shiguang-post' ); ?></strong>
                                        <span><?php echo number_format_i18n($total_posts); ?></span>
                                    </li>
                                     <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-git-pull-request"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M13 6h3a2 2 0 0 1 2 2v7"></path><line x1="6" y1="9" x2="6" y2="21"></line></svg>
                                        <strong><?php _e( '可用空缺ID', 'shiguang-post' ); ?></strong>
                                        <span><?php echo number_format_i18n(count($unused_ids)); ?></span>
                                    </li>
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-database"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                        <strong><?php _e( '待清理冗余数据', 'shiguang-post' ); ?></strong>
                                        <span><?php echo number_format_i18n($revisions_count + $autodrafts_count + $trashed_posts_count); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function enqueue_admin_scripts( $hook ) {
            if ( 'tools_page_shiguang-post' !== $hook ) return;
            
            // 使用 plugins_url() 替代 plugin_dir_url() 来获取路径，这在某些服务器配置下更可靠
            wp_enqueue_style( 'shiguang-post-admin-css', plugins_url( 'assets/admin.css', __FILE__ ), [], '1.3.1' );
            wp_enqueue_script( 'shiguang-post-admin-js', plugins_url( 'assets/admin.js', __FILE__ ), [ 'jquery' ], '1.3.1', true );
            
            wp_localize_script('shiguang-post-admin-js', 'shiguang_ajax_obj', ['ajax_url' => admin_url('admin-ajax.php'),'nonce' => wp_create_nonce('shiguang_ajax_nonce')]);
        }
        
        private function get_unused_ids() {
            global $wpdb;
            $max_id = $wpdb->get_var( "SELECT MAX(ID) FROM {$wpdb->posts}" );
            if (!$max_id) return [];
            $existing_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID BETWEEN 1 AND %d ORDER BY ID", $max_id ) );
            $all_ids = range( 1, $max_id );
            return array_diff( $all_ids, $existing_ids );
        }

        public function ajax_create_post() {
            check_ajax_referer('shiguang_ajax_nonce', 'nonce');
            if (!current_user_can('publish_posts')) wp_send_json_error(['message' => __('无权限操作。', 'shiguang-post')]);
            $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            global $wpdb; // <-- 修复点：将 global $wpdb 移到函数顶部

            // --- 修复开始 ---
            // 使用 $wpdb->get_var 直接查询数据库，绕过对象缓存
            $post_exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d", $post_id ) );

            if ($post_id <= 0 || $post_exists) {
                wp_send_json_error(['message' => __('无效或已被占用的ID。', 'shiguang-post')]);
                return; // 确保在这里停止执行
            }
            // --- 修复结束 ---

            $now = current_time('mysql');
            $result = $wpdb->insert($wpdb->posts, ['ID' => $post_id, 'post_author' => get_current_user_id(), 'post_date' => $now, 'post_date_gmt' => get_gmt_from_date($now),'post_title' => 'id' . $post_id, 'post_status' => 'draft', 'post_name' => $post_id, 'post_modified' => $now,'post_modified_gmt' => get_gmt_from_date($now), 'post_type' => 'post']);

            if ($result === false) {
                wp_send_json_error(['message' => __('数据库插入失败。', 'shiguang-post')]);
            } else {
                $edit_link = get_edit_post_link($post_id, 'raw');
                $message = sprintf(__('成功创建草稿 <a href="%s" target="_blank">id%d</a>！页面将在2秒后刷新。', 'shiguang-post'), $edit_link, $post_id);
                wp_send_json_success(['message' => $message, 'id' => $post_id]);
            }
        }

        public function ajax_cleanup_db() {
            check_ajax_referer('shiguang_ajax_nonce', 'nonce');
            if (!current_user_can('manage_options')) wp_send_json_error(['message' => __('您没有足够的权限执行此操作。', 'shiguang-post')]);

            global $wpdb;
            $action = isset($_POST['cleanup_action']) ? sanitize_key($_POST['cleanup_action']) : '';
            $query = '';
            switch ($action) {
                case 'revisions': $query = "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'"; break;
                case 'autodrafts': $query = "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"; break;
                case 'trashed': $query = "DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'"; break;
                default: wp_send_json_error(['message' => __('无效的清理操作。', 'shiguang-post')]); return;
            }
            $deleted_count = $wpdb->query($query);
            if ($deleted_count === false) {
                 wp_send_json_error(['message' => __('数据库查询失败。', 'shiguang-post')]);
            } else {
                // 在删除后，清理相关的对象缓存
                if ($action === 'revisions') {
                    // 修订版本通常不单独缓存，但清理文章相关的 postmeta 缓存是个好习惯
                    $wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL");
                }
                // 清理回收站和自动草稿后，相关的缓存也应清理
                // 最简单的方法是 flush 掉整个 post 缓存，但这可能影响性能
                // 更好的方式是在 `ajax_cleanup_db` 成功后，也清理下缓存
                // 但因为你用了 location.reload()，缓存问题不大。
                
                $message = sprintf(__('清理成功！共删除了 %s 条数据。页面将在2秒后刷新以更新统计。', 'shiguang-post'), number_format_i18n($deleted_count));
                wp_send_json_success(['message' => $message]);
            }
        }
    }
    new SGP_Shiguang_Post();
}
