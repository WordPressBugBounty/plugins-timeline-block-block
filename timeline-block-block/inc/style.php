<?php
namespace TLGB\Inc;

require_once plugin_dir_path( __FILE__ ) . 'getCSS.php';

// Generate Styles
class TLGBStyleGenerator {
	public $styles = [];
	public function addStyle( $selector, $styles ){
		if( array_key_exists( $selector, $this->styles ) ){
			$this->styles[$selector] = wp_parse_args( $this->styles[$selector], $styles );
		}else { $this->styles[$selector] = $styles; }
	}
	public function renderStyle(){
		$output = '';
		foreach( $this->styles as $selector => $style ){
			$new = '';
			foreach( $style as $property => $value ){
				if( $value == '' ){ $new .= $property; }else { $new .= " $property: $value;"; }
			}
			$output .= "$selector { $new }";
		}
		return $output;
	}
}

class Style{
	static function generatedStyle( $attributes ) {
		extract( $attributes );

		// Generate Styles
		$tlgbStyles = new TLGBStyleGenerator();

		$contentCl = '.timeline__content';
		$mainSl = "#tlgbTimeline-$cId";
		$timelineSl = "$mainSl .timeline";
		$contentSl = "$timelineSl $contentCl";
		$itemSl = "$mainSl .timeline__item";

		$tlgbStyles->addStyle( "$contentSl", [
			'background' => $itemBg,
			'border' => $itemBorder['width'] ." solid ". $itemBorder['color']
		] );
		$tlgbStyles->addStyle( "$contentSl label", [
			'color' => $labelColor
		] );
		$tlgbStyles->addStyle( "$contentSl p", [
			'color' => $itemColor
		] );
		$tlgbStyles->addStyle( "$itemSl::after", [
			'background-color' => $itemBg,
			'border' => '5px solid '. $barDotColor
		] );
		$tlgbStyles->addStyle( "$timelineSl--horizontal .timeline-divider, $timelineSl:not(.timeline--horizontal)::before", [
			'background-color' => $barBackground
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--left $contentCl::before", [
			'border-left' => '11px solid '. $itemBorder['color']
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--right $contentCl::before", [
			'border-right' => '12px solid '. $itemBorder['color']
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--left $contentCl::after", [
			'border-left' => '11px solid '. $itemBg
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--right $contentCl::after", [
			'border-right' => '12px solid '. $itemBg
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--top $contentCl::before", [
			'border-top' => '14px solid '. $itemBorder['color'],
			'border-bottom' => 'none'
		] );
		$tlgbStyles->addStyle( "$itemSl.timeline__item--bottom $contentCl::before", [
			'border-bottom' => '14px solid '. $itemBorder['color'],
			'border-top' => 'none'
		] );
		$tlgbStyles->addStyle( "$itemSl--top $contentCl::after", [
			'border-top' => '12px solid '. $itemBg,
			'border-bottom' => 'none'
		] );
		$tlgbStyles->addStyle( "$itemSl--bottom $contentCl::after", [
			'border-bottom' => '12px solid '. $itemBg,
			'border-top' => 'none'
		] );
		$tlgbStyles->addStyle( "$timelineSl--mobile .timeline__item $contentCl::before", [
			'border-right' => '12px solid '. $itemBorder['color'],
			'border-left' => 'none'
		] );
		$tlgbStyles->addStyle( "$timelineSl--mobile .timeline__item $contentCl::after", [
			'border-right' => '12px solid '. $itemBg,
			'border-left' => 'none'
		] );
		$tlgbStyles->addStyle( "$timelineSl-nav-button", [
			'background-color' => '#fff',
			'border' => '2px solid '. $barBackground
		] );

		ob_start();
			echo GetCSS::getTypoCSS( "$contentSl label", $labelTypo, false )['styles'];
			echo GetCSS::getTypoCSS( "$contentSl p", $itemTypo, false )['styles'];
			echo wp_kses( $tlgbStyles->renderStyle(), [] );

			$tlgbStyles->styles = []; // Empty styles
		return ob_get_clean();
	}
}