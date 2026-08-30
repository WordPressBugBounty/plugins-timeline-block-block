<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (!class_exists('TLGBAdminMenu')) {
  class TLGBAdminMenu {

    function __construct() {
      add_action('admin_menu', [$this, 'adminMenu']);
      add_action('admin_head', [$this, 'adminMenuStyle']);
      add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    public function adminMenu() {
      add_submenu_page(
        'edit.php?post_type=timeline_block',
        __( 'Help & Demos', 'timeline-block' ),
        '<span class="tlgb-menu-highlight">' . esc_html__( 'Help & Demos', 'timeline-block' ) . '</span>',
        'manage_options',
        'tlgb-dashboard',
        [$this, 'renderPage'],
        100
      );
    }

    public function adminMenuStyle() {
      ?>
      <style id="tlgb-admin-menu-style">
        #adminmenu .wp-submenu a .tlgb-menu-highlight,
        #adminmenu a[href="edit.php?post_type=timeline_block&page=tlgb-dashboard"] {
          color: #FF8D28 !important;
          font-weight: 600;
        }

        #adminmenu .wp-submenu a:hover .tlgb-menu-highlight,
        #adminmenu .wp-submenu a:focus .tlgb-menu-highlight,
        #adminmenu .wp-submenu li.current a .tlgb-menu-highlight,
        #adminmenu a[href="edit.php?post_type=timeline_block&page=tlgb-dashboard"]:hover,
        #adminmenu a[href="edit.php?post_type=timeline_block&page=tlgb-dashboard"]:focus,
        #adminmenu li.current a[href="edit.php?post_type=timeline_block&page=tlgb-dashboard"] {
          color: #FF8D28 !important;
        }
      </style>
      <?php
    }

    public function renderPage() {
      ?>
      <div id="tlgbAdminDashboardWrapper"
          data-info='<?php echo esc_attr( wp_json_encode( [
              'version' => TLGB_VERSION,
              'isPremium' => tlgb_fs()->can_use_premium_code(),
              'hasPro' => tlgb_fs()->is_premium(),
              'adminUrl' => admin_url(),
              'deleteDataOnUninstall' => TLGBOptions::getOptions()['delete_data_on_uninstall'],
              'uninstallNonce' => wp_create_nonce( 'tlgbSaveUninstallOption' ),
              // Feeds the Blocks tab (see useBlocksSettings) — without these the
              // tab fires an action-less admin-ajax request and never persists.
              'nonce' => wp_create_nonce( 'tlgb_admin_nonce' ),
              'action' => 'tlgb_get_blocks',
          ] ) ); ?>'
      ></div>
      <?php
    }
    
    public function adminEnqueueScripts($hook) {
      global $post_type, $typenow;
      $current_post_type = $post_type ? $post_type : ( $typenow ? $typenow : ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '' ) );
      if($current_post_type === 'timeline_block' || $current_post_type === 'btimeline') {
        wp_enqueue_style('tlgb-shortcode-column', TLGB_DIR_URL. 'build/column.css', [], TLGB_VERSION);
        wp_enqueue_script('tlgb-shortcode-column', TLGB_DIR_URL. 'build/column.js', ['wp-i18n'], TLGB_VERSION, true);
      }
      if ('timeline_block_page_tlgb-dashboard' === $hook) {
        wp_enqueue_style('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.css', [], TLGB_VERSION);
        $asset_path = TLGB_DIR_PATH . 'build/admin-dashboard.asset.php';
        $asset_file = file_exists( $asset_path ) ? include $asset_path : [];
        $dependencies = ( is_array( $asset_file ) && isset( $asset_file['dependencies'] ) ) ? $asset_file['dependencies'] : [];
        wp_enqueue_script('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.js', array_merge($dependencies, ['wp-util']), TLGB_VERSION, true);
        wp_set_script_translations('tlgb-admin-dashboard', 'timeline-block', TLGB_DIR_PATH . 'languages');
      }
    }
    
  }
}