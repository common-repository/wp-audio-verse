<?php

if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly
/**
 *
 */
class Verse_Single {

	function __construct() {

		add_action('wp_enqueue_scripts', array($this, 'xtl_frontend_script_register'));

		add_action('wp_head', array($this, 'xtl_load_script_on_head') );

		add_action('wp_footer', array($this, 'dfmr_script_load_footer'), 100);

	}

	/**  load css / js on head.
	--------------------------------------------------------------------------------------------------- */
	public function xtl_load_script_on_head() {

		$rtl = esc_attr(get_post_meta(get_the_ID(), 'verse_advance_settings_rtl_main_content', true));
		$rtl_translate = esc_attr(get_post_meta(get_the_ID(), 'verse_advance_settings_rtl_translated_content', true));
		
		$player_placement = esc_attr(get_post_meta(get_the_ID(), 'verse_advance_settings_player_below_content', true));


		?>

		<style>
			<?php if($rtl) { ?>
				.verse-main-content p, .verse-main-content h1, .verse-main-content h2, .verse-main-content h3 {
					direction: rtl;
				}
			<?php } ?>

			<?php if($rtl_translate) { ?>
				.translated-verse p, .translated-verse h1, .translated-verse h2, .translated-verse h3 {
					direction: rtl;
				}
			<?php } ?>

			<?php if($player_placement) { ?>
				.dfm-radio {
					position: static;
				}
			<?php } ?>
		</style>

		<?php
	}

	/**  load css and js load for front end display.
	--------------------------------------------------------------------------------------------------- */
	public function xtl_frontend_script_register() {

		wp_enqueue_style('jquery.mCustomScrollbar', XTL_PLG . '/css/jquery.mCustomScrollbar.css');

		wp_enqueue_style('style', XTL_PLG . '/css/style.css');

		wp_enqueue_script('jplayer-js', XTL_PLG . '/js/jquery.jplayer.min.js', 'jquery', '2.8.3', true);
		wp_enqueue_script('jquery.mCustomScrollbar.concat', XTL_PLG . '/js/jquery.mCustomScrollbar.concat.min.js', 'jquery', '1.0', true);
	}

	/**  jplayer script for play.
	--------------------------------------------------------------------------------------------------- */
	public function dfmr_script_load_footer() {


		global $post;

		$dfmr_url = esc_attr(get_post_meta($post->ID, 'verse_settings_upload_mp3', true));
		$autoplay = esc_attr(get_post_meta(get_the_ID(), 'verse_advance_settings_play_autoplay', true));
		$auto = ($autoplay) ? '.jPlayer("play")' : '';
		$output = '';
		$output .= '<script type="text/javascript">//<![CDATA[';
		$output .= "\n";
		$output .= '
		jQuery(document).ready(function() {jQuery("#dfmr_jquery_jplayer").jPlayer({ready: function() {jQuery(this).jPlayer("setMedia", { mp3: "' . $dfmr_url . '" }) '.$auto.'; }, waiting: function() { jQuery.jPlayer.event.waiting && (jQuery("#pause").hide(), jQuery("#playing").hide(), jQuery("#waiting").show().html("Loading...")) }, playing: function() { jQuery.jPlayer.event.playing && (jQuery("#waiting").hide(), jQuery("#pause").hide(), jQuery("#playing").show().html("Live")) }, pause: function() { jQuery.jPlayer.event.pause && (jQuery("#waiting").hide(), jQuery("#playing").hide(), jQuery("#pause").show().html("Pause")) }, swfPath: "' . XTL_PLG . '/swf/jquery.jplayer.swf", solution: "flash, html", supplied: "mp3, ogg", wmode: "window", preload: "none", oggSupport: !0, smoothPlayBar: !0, keyEnabled: !0 }) });
		';
		$output .= '</script>';
		echo $output;
	}

}

new Verse_Single;