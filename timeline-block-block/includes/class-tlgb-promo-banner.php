<?php

namespace TimelineBlock\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( __NAMESPACE__ . '\TLGB_PromoBanner' ) ) {

    class TLGB_PromoBanner {
        const DISMISSED_KEY   = 'tlgb_promo_banner_dismissed_permanent';
        const EXPIRATION_DATE = '2026-12-05 23:59:59';

        /** Target landing URL */
        const COUPON_URL      = 'https://bplugins.com/products/timeline-block/pricing/?utm_source=admin&utm_medium=dashboard&utm_campaign=bfcm2026';

        public function __construct() {
            add_action( 'admin_notices',                     [ $this, 'render' ] );
            add_action( 'admin_head',                        [ $this, 'enqueue_styles' ] );
            add_action( 'admin_footer',                      [ $this, 'enqueue_scripts' ] );
            add_action( 'wp_ajax_tlgb_dismiss_promo_banner', [ $this, 'ajax_dismiss' ] );
        }

        /* ------------------------------------------------------------------ */
        /*  Screen, Expiration, Dismissal & Pro Helpers                       */
        /* ------------------------------------------------------------------ */

        /**
         * Check if the current admin screen is strictly one of the 2 allowed screens:
         * 1. Timeline Block ShortCodes list (edit.php?post_type=timeline_block)
         * 2. Help & Demos dashboard (edit.php?post_type=timeline_block&page=tlgb-dashboard)
         * Nowhere else.
         */
        private function is_plugin_screen(): bool {
            $screen = get_current_screen();
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

            // Screen 1: Help & Demos dashboard
            if ( 'tlgb-dashboard' === $page ) {
                return true;
            }
            if ( $screen && ( 'timeline_block_page_tlgb-dashboard' === $screen->id || strpos( $screen->id, 'tlgb-dashboard' ) !== false ) ) {
                return true;
            }

            // Screen 2: Timeline Block ShortCodes list table (edit.php?post_type=timeline_block)
            // MUST NOT have any subpage (e.g. tlgb-analytics, tlgb-dashboard) and MUST NOT be post editor / post-new.php
            if ( $screen ) {
                if ( 'edit' === $screen->base && 'timeline_block' === $screen->post_type && empty( $page ) ) {
                    return true;
                }
            }

            global $pagenow;
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
            if ( 'edit.php' === $pagenow && 'timeline_block' === $post_type && empty( $page ) ) {
                return true;
            }

            return false;
        }

        /**
         * Check if current date is past December 5, 2026
         */
        private function is_expired(): bool {
            $expiry_timestamp = strtotime( self::EXPIRATION_DATE );
            $current_time     = time();
            return ( $current_time > $expiry_timestamp );
        }

        /**
         * Check if current user has permanently dismissed the banner
         */
        private function is_dismissed(): bool {
            return (bool) get_user_meta( get_current_user_id(), self::DISMISSED_KEY, true );
        }

        /**
         * Determine if the banner should be omitted
         */
        private function should_skip(): bool {
            // 1. Only show inside Timeline Block admin menu / screens
            if ( ! $this->is_plugin_screen() ) {
                return true;
            }

            // 2. Automatically remove after Dec 5, 2026
            if ( $this->is_expired() ) {
                return true;
            }

            // 3. Never display again once dismissed by the user
            if ( $this->is_dismissed() ) {
                return true;
            }

            // 4. Hide when the Pro add-on plugin is active (not this free plugin)
            if ( defined( 'TLGB_PRO_ACTIVE' ) && TLGB_PRO_ACTIVE ) {
                return true;
            }
            // Also hide if the premium slug folder exists and is active
            if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'timeline-block-block-pro/plugin.php' ) ) {
                return true;
            }

            return false;
        }

        /* ------------------------------------------------------------------ */
        /*  Pro CSS Styles (Design 5)                                         */
        /* ------------------------------------------------------------------ */

        public function enqueue_styles(): void {
            if ( $this->should_skip() ) { return; }
            ?>
            <style id="tlgb-promo-banner-css">
            #tlgb-promo-banner,
            div#tlgb-promo-banner.notice,
            div#tlgb-promo-banner.tlgb-promo-notice {
                --bpl-primary:        #146ef5;
                --bpl-primary-hover:  #0d5ecc;
                --bpl-secondary:      #ff7a00;
                --bpl-dark:           #070127;
                --bpl-text:           #1b2e4b;
                --bpl-muted:          #475569;

                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 8px !important;
                box-shadow: 0 3px 12px rgba(7, 1, 39, 0.05) !important;
                display: flex !important;
                align-items: stretch !important;
                min-height: 84px !important;
                overflow: hidden !important;
                position: relative !important;
                top: auto !important;
                left: auto !important;
                width: auto !important;
                height: auto !important;
                opacity: 1 !important;
                visibility: visible !important;
                margin: 14px 20px 16px 0 !important;
                padding: 0 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
                box-sizing: border-box !important;
                animation: tlgbBannerSlide .3s ease both;
                z-index: 99 !important;
            }
            @keyframes tlgbBannerSlide {
                from { opacity: 0; transform: translateY(-6px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Left Brand Ribbon Block */
            #tlgb-promo-banner .tlgb-brand-ribbon {
                width: 140px;
                min-width: 140px;
                background: linear-gradient(135deg, var(--bpl-primary) 0%, var(--bpl-dark) 100%);
                color: #ffffff;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 12px 10px;
                gap: 6px;
                flex-shrink: 0;
            }
            #tlgb-promo-banner .tlgb-brand-ribbon img {
                width: 38px;
                height: 38px;
                object-fit: contain;
                filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.35));
            }
            #tlgb-promo-banner .tlgb-brand-ribbon .tlgb-ribbon-title {
                font-size: 10.5px;
                font-weight: 800;
                letter-spacing: .7px;
                text-transform: uppercase;
                color: #ffffff;
                text-align: center;
                line-height: 1.2;
            }

            /* Main Content Zone */
            #tlgb-promo-banner .tlgb-content-zone {
                flex: 1;
                padding: 14px 22px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                flex-wrap: wrap;
                padding-right: 38px;
            }

            /* Left Text Stack */
            #tlgb-promo-banner .tlgb-text-stack {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            #tlgb-promo-banner .tlgb-headline {
                font-size: 16px;
                font-weight: 700;
                color: var(--bpl-dark);
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
                line-height: 1.3;
            }
            #tlgb-promo-banner .tlgb-subheadline{
                font-size: 13px;
                font-weight: 500;
                color: #888888;
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
                line-height: 1.3;
            }
            #tlgb-promo-banner .tlgb-discount-chip {
                background: #ffedd5;
                color: #c2410c;
                border: 1px solid #fed7aa;
                font-size: 11px;
                font-weight: 700;
                padding: 2px 8px;
                border-radius: 12px;
                letter-spacing: .2px;
            }

            /* Feature Checklist */
            #tlgb-promo-banner .tlgb-feature-chips {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 12px;
                color: var(--bpl-muted);
                flex-wrap: wrap;
            }
            #tlgb-promo-banner .tlgb-feature-chips span {
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            #tlgb-promo-banner .tlgb-feature-chips .check-icon {
                color: #10b981;
                font-weight: 800;
            }

            /* CTA Action Button */
            #tlgb-promo-banner .tlgb-btn-cta {
                background: var(--bpl-primary);
                color: #ffffff !important;
                padding: 9px 20px;
                border-radius: 5px;
                font-size: 12.5px;
                font-weight: 600;
                text-decoration: none !important;
                box-shadow: 0 2px 8px rgba(20, 110, 245, 0.32);
                transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            #tlgb-promo-banner .tlgb-btn-cta:hover {
                background: var(--bpl-primary-hover);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(20, 110, 245, 0.45);
            }

            /* Dismiss Button (×) */
            #tlgb-promo-banner .tlgb-banner-dismiss-btn {
                position: absolute;
                top: 8px;
                right: 8px;
                background: none;
                border: none;
                cursor: pointer;
                color: #94a3b8;
                font-size: 20px;
                line-height: 1;
                padding: 6px;
                transition: color .15s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
            }
            #tlgb-promo-banner .tlgb-banner-dismiss-btn:hover {
                color: var(--bpl-dark);
            }

            /* Responsive */
            @media (max-width: 960px) {
                #tlgb-promo-banner .tlgb-brand-ribbon {
                    width: 110px;
                    min-width: 110px;
                }
            }
            @media (max-width: 782px) {
                #tlgb-promo-banner {
                    flex-direction: column;
                    align-items: stretch;
                }
                #tlgb-promo-banner .tlgb-brand-ribbon {
                    width: 100%;
                    flex-direction: row;
                    justify-content: flex-start;
                    padding: 8px 16px;
                }
                #tlgb-promo-banner .tlgb-content-zone {
                    flex-direction: column;
                    align-items: flex-start;
                    padding: 14px 16px;
                }
            }
            </style>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  Permanent Dismiss Script (AJAX + Animation)                       */
        /* ------------------------------------------------------------------ */

        public function enqueue_scripts(): void {
            if ( $this->should_skip() ) { return; }
            $dismiss_nonce = wp_create_nonce( 'tlgb_dismiss_promo' );
            $ajax_url      = admin_url( 'admin-ajax.php' );
            ?>
            <script id="tlgb-promo-banner-js">
            (function () {
                var btn = document.getElementById('tlgb-banner-close');
                if (!btn) return;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var banner = document.getElementById('tlgb-promo-banner');
                    if (banner) {
                        banner.style.transition = 'opacity .25s ease, transform .25s ease';
                        banner.style.opacity    = '0';
                        banner.style.transform  = 'translateY(-6px)';
                        setTimeout(function() { banner.remove(); }, 260);
                    }
                    var fd = new FormData();
                    fd.append('action', 'tlgb_dismiss_promo_banner');
                    fd.append('nonce',  '<?php echo esc_js( $dismiss_nonce ); ?>');
                    fetch('<?php echo esc_url( $ajax_url ); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    });
                });
            })();
            </script>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  Render Single Banner (Design 5)                                   */
        /* ------------------------------------------------------------------ */

        public function render(): void {
            if ( $this->should_skip() ) { return; }

            $coupon_url = esc_url( self::COUPON_URL );
            $icon_url   = esc_url( TLGB_DIR_URL . 'assets/images/timeline.png' );
            ?>
            <div id="tlgb-promo-banner" class="notice tlgb-promo-notice">

                <!-- Left Asymmetric Brand Ribbon -->
                <div class="tlgb-brand-ribbon">
                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php esc_attr_e( 'Timeline Block', 'timeline-block' ); ?>" />
                    <span class="tlgb-ribbon-title">Timeline Block</span>
                </div>

                <!-- Main Content Zone -->
                <div class="tlgb-content-zone">
                    <div class="tlgb-text-stack">
                        <div class="tlgb-headline">
                            <span><?php esc_html_e( 'Timeline Block Biggest Black Friday Sale is Live – Up to 45% OFF!', 'timeline-block' ); ?></span>
                        </div>
                        <div class="tlgb-subheadline">
                            <span><?php esc_html_e( 'Upgrade to Timeline Block Pro at an exclusive Black Friday price! Unlock premium templates, stunning animations, interactive timelines, and powerful customization features to bring your stories to life!', 'timeline-block' ); ?></span>
                        </div>
                        <div class="tlgb-feature-chips">
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Pro Timeline Themes', 'timeline-block' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Readymade Timeline Templates', 'timeline-block' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Create Interactive, Zoomable Media-Rich Timelines', 'timeline-block' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Timeline Analytics Dashboard', 'timeline-block' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'AI-Powered Content Generator', 'timeline-block' ); ?></span>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <a href="<?php echo esc_url( $coupon_url ); ?>" target="_blank" rel="noopener noreferrer" class="tlgb-btn-cta">
                        <?php esc_html_e( 'Unlock PRO Features →', 'timeline-block' ); ?>
                    </a>
                </div>

                <!-- Dismiss Button -->
                <button id="tlgb-banner-close"
                        class="tlgb-banner-dismiss-btn"
                        title="<?php esc_attr_e( 'Dismiss', 'timeline-block' ); ?>"
                        aria-label="<?php esc_attr_e( 'Dismiss banner', 'timeline-block' ); ?>">
                    &times;
                </button>

            </div>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  AJAX Handler: Permanent Dismissal                                 */
        /* ------------------------------------------------------------------ */

        public function ajax_dismiss(): void {
            check_ajax_referer( 'tlgb_dismiss_promo', 'nonce' );
            update_user_meta( get_current_user_id(), self::DISMISSED_KEY, 1 );
            wp_send_json_success();
        }
    }

    new TLGB_PromoBanner();
}
