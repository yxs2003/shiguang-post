<?php
/**
 * Plugin Name:       Shiguang Post
 * Plugin URI:        https://www.shiguang.ink/2079
 * Description:       扫描并使用 WordPress 中空缺的文章 ID 来发布文章。
 * Version:           1.1.0
 * Author:            fuhua
 * Author URI:        https://github.com/yxs2003/shiguang-post
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shiguang-post
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // 如果不是 WordPress 环境，直接退出
}

/**
 * 安全检查：确保同名类不存在
 */
if ( ! class_exists( 'SGP_Shiguang_Post' ) ) {

    /**
     * 主插件类
     *
     * @class SGP_Shiguang_Post
     */
    class SGP_Shiguang_Post {

        /**
         * 构造函数
         */
        public function __construct() {
            // 添加后台菜单
            add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
            // 加载后台脚本和样式
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
            // 注册 AJAX 处理函数
            add_action( 'wp_ajax_shiguang_create_post', [ $this, 'ajax_create_post' ] );
        }

        /**
         * 添加后台菜单项
         */
        public function add_admin_menu() {
            add_management_page(
                '时光机文章',           // 页面标题
                '时光机文章',           // 菜单标题
                'publish_posts',      // 所需权限
                'shiguang-post',      // 菜单 slug
                [ $this, 'render_options_page' ] // 渲染页面的回调函数
            );
        }

        /**
         * 渲染设置页面
         */
        public function render_options_page() {
            ?>
            <div class="wrap shiguang-post-wrap">
                <h1><?php _e( '时光机文章 - 未使用ID列表', 'shiguang-post' ); ?></h1>
                <p><?php _e( 'WordPress 中的文章ID在创建后是固定的，即使文章被删除，ID也不会被回收。本插件可以扫描数据库，找出那些被跳过或已删除文章留下的空缺ID。', 'shiguang-post' ); ?></p>
                <p><?php _e( '您可以选择一个未使用的ID，点击“创建草稿”按钮，系统将使用该ID为您创建一篇标题为 "idxx" 的新草稿。', 'shiguang-post' ); ?></p>
                
                <div id="shiguang-action-feedback" class="notice" style="display:none;"></div>

                <div class="id-list-container">
                    <h2><?php _e( '可用的文章 ID', 'shiguang-post' ); ?></h2>
                    <div id="shiguang-id-list" class="shiguang-id-list">
                        <?php
                        $unused_ids = $this->get_unused_ids();
                        if ( ! empty( $unused_ids ) ) {
                            foreach ( $unused_ids as $id ) {
                                echo '<div class="id-card" id="id-card-' . esc_attr( $id ) . '">';
                                echo '<span>ID: <strong>' . esc_html( $id ) . '</strong></span>';
                                echo '<button class="button button-primary create-draft-btn" data-id="' . esc_attr( $id ) . '">' . __( '创建草稿', 'shiguang-post' ) . '</button>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>' . __( '未发现可用的空缺ID。', 'shiguang-post' ) . '</p>';
                        }
                        ?>
                    </div>
                </div>
                <?php // 添加 nonce 字段用于安全验证 ?>
                <?php wp_nonce_field( 'shiguang_create_post_nonce', 'shiguang_nonce' ); ?>
            </div>
            <?php
        }

        /**
         * 加载后台脚本和样式
         *
         * @param string $hook 当前页面的钩子
         */
        public function enqueue_admin_scripts( $hook ) {
            // 仅在我们的插件页面加载
            if ( 'tools_page_shiguang-post' !== $hook ) {
                return;
            }
            // 加载 CSS
            wp_enqueue_style(
                'shiguang-post-admin-css',
                plugin_dir_url( __FILE__ ) . 'assets/admin.css',
                [],
                '1.1.0'
            );
            // 加载 JS
            wp_enqueue_script(
                'shiguang-post-admin-js',
                plugin_dir_url( __FILE__ ) . 'assets/admin.js',
                [ 'jquery' ],
                '1.1.0',
                true // 在 body 底部加载
            );
            // 将 ajax url 和 nonce 传递给 JS
            wp_localize_script('shiguang-post-admin-js', 'shiguang_ajax_obj', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('shiguang_create_post_nonce')
            ]);
        }
        
        /**
         * 获取未使用的文章ID
         * @return array 未使用的ID数组
         */
        private function get_unused_ids() {
            global $wpdb;
            $unused_ids = [];

            // 获取最大的文章ID
            $max_id = $wpdb->get_var( "SELECT MAX(ID) FROM {$wpdb->posts}" );
            if (!$max_id) {
                return [];
            }

            // 获取所有已存在的ID
            $existing_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID BETWEEN 1 AND %d ORDER BY ID", $max_id ) );
            
            // 生成一个从1到最大ID的完整序列
            $all_ids = range( 1, $max_id );

            // 找出差集，即未被使用的ID
            $unused_ids = array_diff( $all_ids, $existing_ids );

            return $unused_ids;
        }

        /**
         * AJAX 请求处理：创建草稿
         */
        public function ajax_create_post() {
            // 安全检查
            check_ajax_referer( 'shiguang_create_post_nonce', 'nonce' );

            // 检查用户权限
            if ( ! current_user_can( 'publish_posts' ) ) {
                wp_send_json_error( [ 'message' => __( '您没有权限执行此操作。', 'shiguang-post' ) ] );
            }

            // 获取并验证 ID
            $post_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
            if ( $post_id <= 0 ) {
                wp_send_json_error( [ 'message' => __( '无效的ID。', 'shiguang-post' ) ] );
            }

            // 检查该ID是否真的未使用
            if ( get_post( $post_id ) ) {
                wp_send_json_error( [ 'message' => __( '此ID已被占用，请刷新页面重试。', 'shiguang-post' ) ] );
            }

            global $wpdb;
            $current_user_id = get_current_user_id();
            $post_title = 'id' . $post_id;
            $now = current_time( 'mysql' );
            $now_gmt = current_time( 'mysql', 1 );

            // 直接通过 SQL 插入，以强制使用指定 ID
            $result = $wpdb->insert(
                $wpdb->posts,
                [
                    'ID'              => $post_id,
                    'post_author'     => $current_user_id,
                    'post_date'       => $now,
                    'post_date_gmt'   => $now_gmt,
                    'post_content'    => '',
                    'post_title'      => $post_title,
                    'post_status'     => 'draft',
                    'comment_status'  => 'closed',
                    'ping_status'     => 'closed',
                    'post_name'       => $post_id, // 默认使用ID作为别名
                    'post_modified'   => $now,
                    'post_modified_gmt' => $now_gmt,
                    'post_parent'     => 0,
                    'menu_order'      => 0,
                    'post_type'       => 'post',
                    'comment_count'   => 0,
                ],
                [
                    '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d',
                ]
            );

            if ( $result === false ) {
                wp_send_json_error( [ 'message' => __( '数据库插入失败，无法创建草稿。', 'shiguang-post' ) ] );
            } else {
                $edit_link = get_edit_post_link( $post_id, 'raw' );
                $message = sprintf(
                    __( '成功创建草稿！标题: %s (ID: %d)。 <a href="%s" target="_blank">点此编辑</a>', 'shiguang-post' ),
                    $post_title,
                    $post_id,
                    $edit_link
                );
                wp_send_json_success( [ 'message' => $message ] );
            }
        }
    }

    // 实例化插件
    new SGP_Shiguang_Post();
}