<?php
require_once plugin_dir_path( __FILE__ ) . 'style.php';

class TLGBBlock{
	function __construct(){
		add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
		add_action( 'init', [$this, 'register'] );
	}

	function enqueueBlockAssets(){ 
		wp_register_script( 'timelineJS', TLGB_DIR_URL . 'assets/js/timeline.min.js', [], TLGB_VERSION, true );
	}

	function register() {
		wp_register_style( 'tlgb-b-timeline-block-style', TLGB_DIR_URL . 'dist/style.css', [], TLGB_VERSION ); // Style
		wp_register_style( 'tlgb-b-timeline-block-editor-style', TLGB_DIR_URL . 'dist/editor.css', [ 'tlgb-b-timeline-block-style' ], TLGB_VERSION ); // Backend Style

		register_block_type( __DIR__, [
			'editor_style'		=> 'tlgb-b-timeline-block-editor-style',
			'render_callback'	=> [$this, 'render']
		] ); // Register Block

		wp_set_script_translations( 'tlgb-b-timeline-block-editor-script', 'timeline-block', TLGB_DIR_PATH . 'languages' );
	}

	function render( $attributes ){
		extract( $attributes );

		wp_enqueue_style( 'tlgb-b-timeline-block-style' );
		wp_enqueue_script( 'tlgb-b-timeline-block-script', TLGB_DIR_URL . 'dist/script.js', [ 'timelineJS' ], TLGB_VERSION, true );
		wp_set_script_translations( 'tlgb-b-timeline-block-script', 'timeline-block', TLGB_DIR_PATH . 'languages' );

		ob_start(); ?>
		<div class='wp-block-tlgb-b-timeline-block <?php echo 'align' . esc_attr( $align ); ?>' id='tlgbTimeline-<?php echo esc_attr( $cId ); ?>' data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'>
			<style>
				<?php echo wp_kses( TLGB\Inc\Style::generatedStyle( $attributes ), [] ); ?>
			</style>

			<div class='timeline tlgbTimeline'>
				<div class='timeline__wrap'>
					<div class='timeline__items'>
						<?php foreach ( $timelines as $index => $timeline ) { extract( $timeline ); ?>
							<div class='timeline__item fadeIn' id='tlgbTimelineItem-<?php echo esc_attr( $index ); ?>'>
								<div class='timeline__content'>
									<label><?php echo wp_kses_post( $label ); ?></label>

									<p><?php echo wp_kses_post( $description ); ?></p>
								</div>
							</div> <!-- Timeline Item -->
						<?php } ?>
					</div> <!-- Timeline Items -->
				</div> <!-- Timeline Wrap -->
			</div> <!-- Timeline -->
		</div> <!-- Timeline Block -->

		<?php return ob_get_clean();
	} // Render
}
new TLGBBlock();