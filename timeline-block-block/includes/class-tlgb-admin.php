<?php

if (!class_exists('TLGBAdminMenu')) {
  class TLGBAdminMenu {

    function __construct() {
      add_action('admin_menu', [$this, 'adminMenu']);
      add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    public function adminMenu() {
      add_submenu_page(
        'edit.php?post_type=timeline_block',
        'Demo & Help',
        'Demo & Help',
        'manage_options',
        'tlgb-dashboard',
        [$this, 'renderPage'],
        100
      );
    }

    public function renderPage() {
      ?>
       <div id="tlgbAdminDashboardWrapper"
            data-info='<?php echo esc_attr( wp_json_encode( [
                'version' => TLGB_VERSION,
                'isPremium' => esc_attr(tlgbIsPremium()),
                'adminUrl' => admin_url()
            ] ) ); ?>'>
        </div>
      <?php
    }
    
    public function adminEnqueueScripts($hook) {
      global $post_type;
      if($post_type === 'timeline_block' || $post_type === 'btimeline') {
        wp_enqueue_style('tlgb-shortcode-column', TLGB_DIR_URL. 'build/column.css', [], TLGB_VERSION);
        wp_enqueue_script('tlgb-shortcode-column', TLGB_DIR_URL. 'build/column.js', [], TLGB_VERSION, true);
      }
      if ('timeline_block_page_tlgb-dashboard' === $hook) {
        wp_enqueue_style('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.css', [], TLGB_VERSION);
        wp_enqueue_script('tlgb-admin-dashboard', TLGB_DIR_URL . 'build/admin-dashboard.js', ['react', 'react-dom'], TLGB_VERSION, true);
        wp_set_script_translations('tlgb-admin-help', 'timeline-block', TLGB_DIR_PATH . 'languages');
      }
    }
    
  }
  new TLGBAdminMenu();
}