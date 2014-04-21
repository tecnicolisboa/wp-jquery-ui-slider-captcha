<?php
/*
Plugin Name: Slider Captcha
Plugin URL: http://nme.ist.utl.pt
Description: Slider Captcha is a module that will replace all the captcha from WordPress. 
Version: 0.5.4
Author: NME - Núcleo de Multimédia e E-Learning.
Author URI: http://nme.ist.utl.pt
Text Domain: slider_captcha
*/

// plugin folder url
if(!defined('SLIDER-CAPTCHA-URL')) {
	define('SLIDER-CAPTCHA-URL', plugin_dir_url( __FILE__ ));
}

if(!defined('SLIDER-CAPTCHA-PATH')) {
	define('SLIDER-CAPTCHA-PATH', plugin_dir_path( __FILE__ ));
}

class SliderCaptcha {

	public $js_settings;

	public $settings;

	function __construct() {

		//Load the languages
		add_action('init' ,array(&$this, 'lang_init'));

		//Load all the scripts required
		add_action( 'wp_enqueue_scripts', array(&$this, 'register_scripts' ));

		//Draw the captcha on the comment form
		add_action('comment_form', array($this,'render_slider_on_comments'));

		//Validate the captcha after comment is made
        add_filter('preprocess_comment', array($this, 'hook_validate_slider'));

	}

	function init_default() {
		//Slider default settings
		$this->js_settings = array(
			'hintText' => __('Swipe to Validate','slider_captcha'),
			'textAfterUnlock' => __("You can now Submit",'slider_captcha'),
			'events' => array(
				'validateOnServer' => true,
				),
			);
		$this->settings = array(
			'containerClass' => null,
			);
	}

	/**
	 * Initialize the language domain of the plugin
	 */
	function lang_init() {
	   if (function_exists('load_plugin_textdomain')) {
	       load_plugin_textdomain( 'slider_captcha', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/');
	   }
	   // Initialize the default settings
       $this->init_default();

	}

	public function register_scripts() {

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-mouse');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-touch-punch', plugins_url( '/js/jquery.ui.touch-punch-improved.js', __FILE__ ), array('jquery-ui-core','jquery-ui-mouse','jquery'), '0.3.1',false);
	
		wp_enqueue_script('jquery-slider-captcha', plugins_url( '/js/slider-captcha.min.js', __FILE__ ), array('jquery-ui-core','jquery-ui-touch-punch'), '0.2.1',false);
		
		wp_enqueue_style('slider-captcha-css', plugins_url( '/css/slider-captcha.css', __FILE__ ), '0.2.1' );
	}

	/**
	 * Function that will render the Slider Captcha
	 * @return echos the code
	 */
	public function render_slider_on_comments($post_id) {
		if($settings == null)
            $settings = array_merge($this->js_settings, $this->settings);
	    else
            $settings = array_merge($this->js_settings, $this->settings, $settings);

		?>
		<script type="text/javascript">
		jQuery(function($) {
			$( document ).ready(function() {

				if($("#commentform .form-submit").before('<p id="auto_slidercaptcha"></p>')) {
					//Load the slider captcha
					$("#auto_slidercaptcha").sliderCaptcha(<?=json_encode($settings)?>);
				}
			});
		});
		</script>
		<?
	}
	

	public function hook_validate_slider($comment_data) {
		$validateOnServer = $_POST['slider_captcha_validated'];
		if( $validateOnServer != 1)
			wp_die(__("<strong>ERROR:</strong> Something went wrong with the CAPTCHA validation... Please make sure you have Javascript enabled on your browser.",'slider_captcha'));
		return $comment_data;
	}

}

$GLOBALS['sliderCaptcha'] = new SliderCaptcha();

function slider_captcha($container = 'p', $settings = null) {
	global $sliderCaptcha;
	if($settings == null)
		$settings = array_merge($sliderCaptcha->js_settings, $sliderCaptcha->settings);
	else
		$settings = array_merge($sliderCaptcha->js_settings, $sliderCaptcha->settings, $settings);

	$container_class = (isset($settings['containerClass']) && $settings['containerClass']!=NULL 
		? 'class="' . $settings['containerClass'] . '"' : '');

	?>
		<<?=$container?> <?=$container_class?> id="slidercaptcha"> </<?=$container?>>
		<script type="text/javascript">
		jQuery(function($) {
			$( document ).ready(function() {
				//Load the slider captcha
				$("#slidercaptcha").sliderCaptcha(<?=json_encode($settings)?>);
			});
		});
		</script>
	<?

}