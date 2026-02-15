<?php
if(!class_exists('tlgbCPT')) {
    class tlgbCPT {
        public function __construct() {
            add_action('init', [$this, 'registerCPT']);
            add_filter('manage_timeline_block_posts_columns', [$this, 'tlgb_timelineBlockManageColumns'], 10);
            add_action('manage_timeline_block_posts_custom_column', [$this, 'tlgb_timelineBlockManageCustomColumns'], 10, 2);
        }
    
        public function registerCPT() {
            register_post_type('timeline_block', [
                'labels' => [
                    'name' => 'Timeline Block',
                    'singular_name' => 'Timeline',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Timeline',
                    'edit_item' => 'Edit Timeline',
                    'not_found' => 'There was no tour please add one',
                    'search_items' => 'Search Timeline',
                    'view_item' => 'View Timeline',
                    'menu_name' => 'Timeline',
                    'all_items' => 'Timeline Block ShortCodes',
                    'not_found_in_trash' => 'No Timeline found in trash',
                    'item_updated' => 'Timeline updated',
                ],
                'public' => true,
                'has_archive' => true,
                "show_in_rest" => true,
                "template_lock" => "all",
                'menu_icon' => TLGB_DIR_URL . 'assets/images/timeline.png',
                "template" => [["tlgb/b-timeline-block"]],
                'show_in_menu' => true,
            ]);
            
        }
    
        public function tlgb_timelineBlockManageColumns($defaults){
            unset($defaults['date']);
            $defaults['shortcode'] = 'ShortCode';
            $defaults['date'] = 'Date';
            return $defaults;
        }
    
        public function tlgb_timelineBlockManageCustomColumns($column_name, $post_ID){
            if ($column_name == 'shortcode') {
                echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr($post_ID) . '">
                        <input value="[timeline_block id=' . esc_attr($post_ID) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr($post_ID) . '\')" readonly>
                        <span class="tooltip">Copy To Clipboard</span>
                      </div>';
            }
        }
    }

    new tlgbCPT();
}