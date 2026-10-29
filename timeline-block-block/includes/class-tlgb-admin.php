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

      // Same slot and label as the Pro Analytics page, so the menu matches
      // after upgrading. Here it is a preview that leads to Pricing.
      add_submenu_page(
        'edit.php?post_type=timeline_block',
        __( 'Timeline Analytics', 'timeline-block' ),
        esc_html__( 'Analytics', 'timeline-block' ) . ' <span class="tlgb-menu-new">' . esc_html__( 'New', 'timeline-block' ) . '</span>',
        'manage_options',
        'tlgb-analytics',
        [$this, 'renderAnalyticsPage'],
        2
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

        #adminmenu .wp-submenu a .tlgb-menu-new {
          display: inline-block;
          margin-left: 6px;
          padding: 1px 7px;
          background: #3b54f6;
          color: #fff;
          font-size: 10px;
          font-weight: 700;
          line-height: 1.6;
          letter-spacing: 0.04em;
          text-transform: uppercase;
          vertical-align: 1px;
          border-radius: 3px;
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
    
    /**
     * Analytics is Pro-only: show a screenshot of the Pro dashboard under an
     * overlay. Clicking anywhere on it opens Pricing in the admin dashboard.
     */
    public function renderAnalyticsPage() {
      $pricing_url = admin_url( 'edit.php?post_type=timeline_block&page=tlgb-dashboard#/pricing' );
      ?>
      <div class="wrap tlgbAnalyticsPreview">
        <h1><?php esc_html_e( 'Timeline Analytics', 'timeline-block' ); ?></h1>
        <p class="tlgbAnalyticsPreview__intro"><?php esc_html_e( 'See views, engaged readers, read depth, completion rate and clicks for every timeline.', 'timeline-block' ); ?></p>

        <a class="tlgbAnalyticsPreview__card" href="<?php echo esc_url( $pricing_url ); ?>">
          <img src="<?php echo esc_url( TLGB_DIR_URL . 'assets/images/analytics-preview.webp' ); ?>" width="1760" height="888" alt="<?php esc_attr_e( 'Timeline Analytics dashboard preview', 'timeline-block' ); ?>" />
          <span class="tlgbAnalyticsPreview__overlay">
            <span class="tlgbAnalyticsPreview__button">
              <span class="dashicons dashicons-lock" aria-hidden="true"></span>
              <?php esc_html_e( 'Premium Feature', 'timeline-block' ); ?>
            </span>
          </span>
        </a>
      </div>

      <style>
        .tlgbAnalyticsPreview__intro {
          margin: 4px 0 16px;
          color: #50575e;
          font-size: 14px;
        }

        .tlgbAnalyticsPreview__card {
          position: relative;
          display: block;
          max-width: 1760px;
          overflow: hidden;
          border: 1px solid #dcdcde;
          border-radius: 8px;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .tlgbAnalyticsPreview__card img {
          display: block;
          width: 100%;
          height: auto;
          filter: blur(1.5px);
          transition: filter 0.2s ease;
        }

        .tlgbAnalyticsPreview__overlay {
          position: absolute;
          inset: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(15, 23, 42, 0.45);
          transition: background 0.2s ease;
        }

        .tlgbAnalyticsPreview__button {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 14px 28px;
          background: #FF8D28;
          color: #fff;
          font-size: 16px;
          font-weight: 600;
          border-radius: 6px;
          box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
          transition: transform 0.2s ease, background 0.2s ease;
        }

        .tlgbAnalyticsPreview__button .dashicons {
          font-size: 18px;
          width: 18px;
          height: 18px;
        }

        .tlgbAnalyticsPreview__card:hover .tlgbAnalyticsPreview__overlay,
        .tlgbAnalyticsPreview__card:focus .tlgbAnalyticsPreview__overlay {
          background: rgba(15, 23, 42, 0.55);
        }

        .tlgbAnalyticsPreview__card:hover .tlgbAnalyticsPreview__button,
        .tlgbAnalyticsPreview__card:focus .tlgbAnalyticsPreview__button {
          background: #f07a12;
          transform: translateY(-2px);
        }
      </style>
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