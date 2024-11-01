<?php
/*
Plugin Name: WP Audio Verse
Plugin URI: http://codextune.com
Description: WP Audio Verse is a WordPress Plugin which help to publish online book viewer with audio. Publish Holy Quran with Audio and translation facilities. Also its help to publish holy bible, verse, sermons, lyrics, poem etc.
Author: Jahirul Islam Mamun
Author URI: http://www.jimamun.com
Version: 2.0.0
Text Domain: verse
 */

if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly

/**
 * define
 */
class Xtl_Verse_Reader {

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 */
	public $plugin_path;

	function __construct() {

		define('XTL_PLG', $this->plugin_url());
		define('XTL_DIR', dirname(__FILE__));

		//include front end player
		$this->xtl_frontend_include();
		//declire metabox
		$this->xtl_declire_settings();

		// single page
		add_filter( 'single_template', array($this, 'get_custom_post_type_template') );


	}

	/*
	 * Show content in front end
	 */
	public function xtl_frontend_include() {
		require_once XTL_DIR . '/lib/custom-post-type.php';
		require_once XTL_DIR . '/view/filter-single.php';
	}

	public function xtl_declire_settings() {
		$verse = new Xtl_Post_Type('verse', array("menu_icon" => 'dashicons-admin-network'));

		$verse->add_meta_box('Verse Settings', array(
			'Verse translate' => 'textarea',
			'Upload MP3' => 'upload'
			));

		$verse->add_meta_box('Verse Advance Settings', array(
			'RTL Main Content' => 'checkbox',
			'RTL Translated Content' => 'checkbox',
			'Play Autoplay'	=> 'checkbox',
			'Player Below Content' => 'checkbox'
			));
	}


	public function get_custom_post_type_template($single_template) {
		global $post;

		if ($post->post_type == 'verse') {
			$single_template = XTL_DIR . '/view/single-verse.php';
		}
		return $single_template;
	}


	/**
	 * Plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		if ($this->plugin_url) {
			return $this->plugin_url;
		}

		return $this->plugin_url = untrailingslashit(plugins_url('', __FILE__));
	}

	/**
	 * Plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		if ($this->plugin_path) {
			return $this->plugin_path;
		}

		return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
	}

}

$verse = new Xtl_Verse_Reader();