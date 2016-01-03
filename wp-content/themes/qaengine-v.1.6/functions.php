<?php
define("ET_UPDATE_PATH", "http://update.enginethemes.com/?do=product-update");
define("ET_VERSION", '1.6');

if(!defined('ET_URL'))
	define('ET_URL', 'http://www.enginethemes.com/');

if(!defined('ET_CONTENT_DIR'))
	define('ET_CONTENT_DIR', WP_CONTENT_DIR.'/et-content/');

define( 'TEMPLATEURL', get_template_directory_uri() );
define( 'THEME_NAME' , 'qaengine');
define( 'ET_DOMAIN'  , 'enginetheme');

if(!defined('THEME_CONTENT_DIR ')) 	define('THEME_CONTENT_DIR', WP_CONTENT_DIR . '/et-content' . '/qaengine' );
if(!defined('THEME_CONTENT_URL'))	define('THEME_CONTENT_URL', content_url() . '/et-content' . '/qaengine' );

// theme language path
if(!defined('THEME_LANGUAGE_PATH') ) define('THEME_LANGUAGE_PATH', THEME_CONTENT_DIR.'/lang/');

if(!defined('ET_LANGUAGE_PATH') )
 define('ET_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang');

if(!defined('ET_CSS_PATH') )
 define('ET_CSS_PATH', THEME_CONTENT_DIR . '/css');

// if (!defined('USE_SOCIAL')) define('USE_SOCIAL', 1);

require_once TEMPLATEPATH.'/includes/index.php';
require_once TEMPLATEPATH.'/mobile/functions.php';

try {
	// if(USE_SOCIAL){
 //        init_social_login();
 //    }

	if ( is_admin() ){
		new QA_Admin();
	} else {
		new QA_Front();
	}
} catch (Exception $e) {
	echo $e->getMessage();
}
add_filter('gettext', 'et_translate_text' , 99, 3);
function et_translate_text ( $translated_text, $text, $domain ) {
	$translation = array (
		'YOU MUST <a class="authenticate" href="%s">LOGIN</a> TO SUBMIT A REVIEW' => 'YOU HAVE TO <a class="authenticate" href="%s">SIGNIN</a> TO CREATE A REVIEW',
	);
	if( isset( $translation[$text] ) ) {
		return $translation[$text];
	}
	return $translated_text;
}
//remove_action('admin_init', 'block_dashboard');

// Redirect to page Intro when register by the "Back door" (/wp-login.php?action=register)
if(is_multisite()){
	add_filter( 'wp_signup_location', 'my_custom_signup_location' );
	function my_custom_signup_location( $url ) {
	    return et_get_page_link('intro');
	}
}else{
	add_filter( 'registration_redirect', 'wpse_46848_hijack_the_back' );
	function wpse_46848_hijack_the_back( $redirect_to ) {
	    wp_redirect( et_get_page_link('intro') );
	    return exit();
	}
}

/**
 * Add custom metadata to user
 * @param array $meta_data
 * @return array $meta_data
 *
 * @since 1.5.4
 * @author tatthien
 */
function add_user_metakey( $meta_data ) {
	$meta_data = wp_parse_args( array( 'qa_point' ), $meta_data );
	return $meta_data;
}

add_filter( 'ae_define_user_meta', 'add_user_metakey' );

/**
 * Add styles for user point input in admin setting
 * @param void
 * @return void
 *
 * @since 1.5.4
 * @author tatthien
 */
function add_styles_for_user_point_setting() {
	$current_screen = get_current_screen();

	// Instert styles for memeber setting page only
	if( $current_screen->base == 'engine-settings_page_et-users' ) {
		?>
		<style>
			.et-act {
				width: auto;
			}

			.user-points {
				display: inline-block;
	    		width: 120px;
			}

			.user-points input {
				max-width: 50%;
			    text-align: center;
			    width: auto;
			    height: 28px;
			    position: relative;
			    top: 1px;
			}
		</style>
		<?php
	}
}

add_action( 'admin_head', 'add_styles_for_user_point_setting' );

/**
 * Add js template to members section of admin setting
 * @param void
 * @return void
 *
 * @since 1.5.4
 * @author tatthien
 */
function add_admin_user_js_template() {
	?>
	<span class="user-points">
		<input type="text" value="{{= qa_point }}" class="regular-input" name="qa_point" /> <?php _e('Points', ET_DOMAIN) ?>
	</span>
	<?php
}

add_action( 'ae_admin_user_js_template_action', 'add_admin_user_js_template' );

/**
 * Add schema for thumbnail - itemprop="image"
 * @param array $attr
 * @param int $attachment
 * @param string $size
 * @return array $attr
 *
 * @since 1.5.6
 * @author tatthien
 */
function add_itemprop_to_post_thumbnail($attr, $attachment, $size) {
	$attr = wp_parse_args($attr, array('itemprop'=>'image'));
	return $attr;
}

add_filter( 'wp_get_attachment_image_attributes', 'add_itemprop_to_post_thumbnail', 10, 3);

/**
 * Add schema itemlist for questions list
 * @param void
 * @return void
 *
 * @since 1.5.6
 * @author tatthien
 */
if(!function_exists('add_schema_for_question_list')) {
	function add_schema_for_question_list() {
		$current_url = qa_get_current_url();
		echo '<link itemprop="url" href="'. $current_url .'" style="display: none;"/>';
	}
}

add_action('qa_top_questions_listing', 'add_schema_for_question_list');

/**
 * Get current url
 * @param void
 * @return string $current_url
 *
 * @since 1.5.6
 * @author tatthien
 */
if(!function_exists('qa_get_current_url')) {
	function qa_get_current_url() {
		global $wp;
		$current_url = home_url(add_query_arg(array(),$wp->request));
		return $current_url;
	}
}
