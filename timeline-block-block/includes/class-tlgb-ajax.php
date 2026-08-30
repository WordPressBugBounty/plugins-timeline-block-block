<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TLGBAjax' ) ) {
	class TLGBAjax {

		public function __construct() {
			add_action( 'wp_ajax_tlgb_get_blocks', [ $this, 'tlgb_get_blocks' ] );
		}

		/**
		 * Read/write the list of disabled blocks for the dashboard Blocks tab.
		 *
		 * - Called with no `data` payload -> returns the saved list (read).
		 * - Called with a `data` payload  -> saves the list (write).
		 *
		 * The list stores block folder basenames (e.g. "media-story-timeline")
		 * plus "b-timeline-block" for the free block.
		 */
		public function tlgb_get_blocks() {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'tlgb_admin_nonce' ) ) {
				wp_send_json_error( __( 'Invalid Request', 'timeline-block' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Permission denied', 'timeline-block' ) );
			}

			// Read path: no data posted -> return the currently disabled blocks.
			if ( ! isset( $_POST['data'] ) ) {
				wp_send_json_success( (array) get_option( 'tlgb_disabled_blocks', [] ) );
			}

			$data = json_decode( sanitize_text_field( wp_unslash( $_POST['data'] ) ), true );

			if ( ! is_array( $data ) ) {
				$data = [];
			}

			$data = array_values( array_map( 'sanitize_text_field', $data ) );

			update_option( 'tlgb_disabled_blocks', $data );
			wp_send_json_success( $data );
		}
	}
}
