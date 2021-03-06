<?php
define( "TEMP_URL" , get_bloginfo('template_url'));
define( "DEVVN_VERSION" ,'1.0');
define( "DEVVN_DEV_MODE" ,true);
if( class_exists('acf') ) { 
	define('GOOLE_MAPS_API', get_field('google_maps_api','option'));
}

require_once get_template_directory() . '/inc/class-tgm-plugin-activation.php';
require get_template_directory() . '/inc/aq_resizer.php';
require get_template_directory() . '/inc/copyright/copyright_svl.php';
//require get_template_directory() . '/inc/woocommerce_int/woo_int.php';
require get_template_directory() . '/inc/style_script_int.php';

function my_acf_google_map_api( $api ){	
	$api['key'] = GOOLE_MAPS_API;	
	return $api;	
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
/*
 * Setup theme
 */
function devvn_setup() {
	load_theme_textdomain( 'devvn', get_template_directory() . '/languages' );
	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );
	//Add thumbnail to post
	add_theme_support( 'post-thumbnails' );
	//Shortcode in widget
	add_filter('widget_text', 'do_shortcode');
    add_editor_style();
	//menu
	/********
	 * Call: wp_nav_menu(array('theme_location'  => 'header','container'=> ''));
	 * *********/
	register_nav_menus( array(
		'header' => __( 'Header menu', 'devvn' ),
		'footer'  => __( 'Footer menu', 'devvn' ),
	));
	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );
	//Remove version
	remove_action('wp_head', 'wp_generator');
	//Remove Default WordPress Image Sizes
	function svl_remove_default_image_sizes( $sizes) {
		//unset( $sizes['thumbnail']);
		unset( $sizes['medium']);
		unset( $sizes['large']);
		unset( $sizes['medium_large']);
		 
		return $sizes;
	}
	add_filter('intermediate_image_sizes_advanced', 'svl_remove_default_image_sizes');
	if ( function_exists( 'add_image_size' ) ) {
		//add_image_size( 'homepage-thumb', 50, 50, true ); //(cropped)
	}
}
add_action( 'after_setup_theme', 'devvn_setup' );
//Sidebar
/*
 <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('main-sidebar')) : ?><?php endif; ?>
 */
add_action( 'widgets_init', 'theme_slug_widgets_init' );
function theme_slug_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Main Sidebar', 'devvn' ),
        'id' => 'main-sidebar',        
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="title-sidebar">',
		'after_title'   => '</h3>',
    ));
}
//Title
function svl_wp_title( $title, $sep ) {
	global $paged, $page;
	if ( is_feed() )
		return $title;
	$title .= get_bloginfo( 'name', 'display' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() )
		$title = "$title $sep " . sprintf( __( 'Trang %s', 'devvn' ), max( $paged, $page ) );
	return $title;
}
add_filter( 'wp_title', 'svl_wp_title', 10, 2 );

// Add specific CSS class by filter
add_filter( 'body_class', 'devvn_mobile_class' );
function devvn_mobile_class( $classes ) {
	if(wp_is_mobile()){
		$classes[] = 'devvn_mobile';
	}else{
		$classes[] ="devvn_desktop";
	}
	return $classes;
}
/* ACF 4
//Theme Options
function my_acf_options_page_settings( $settings )
{
	$settings['title'] = 'Theme Options';
	$settings['pages'] = array('General');

	return $settings;
}

add_filter('acf/options_page/settings', 'my_acf_options_page_settings');
*/
//Theme Options
if( function_exists('acf_add_options_page') ) {
 
	$option_page = acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title' 	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability' 	=> 'edit_posts',
		'redirect' 	=> false
	));
	/*acf_add_options_sub_page(array(
		'page_title' 	=> 'Shop Page Setting',
		'menu_title' 	=> 'Social',
		'parent_slug' 	=> $parent['menu_slug'],
	));*/
 
}

//Code phan trang
function wp_corenavi_table($main_query = null) {
		global $wp_query;
		if(!$main_query) $main_query = $wp_query;
		$big = 999999999; 
		$total = $main_query->max_num_pages;
		if($total > 1) echo '<div class="paginate_links">';
		echo paginate_links( array(
			'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' 	=> '?paged=%#%',
			'current' 	=> max( 1, get_query_var('paged') ),
			'total' 	=> $total,
			'mid_size'	=> '10',
			'prev_text'    => __('Trang tr?????c','devvn'),
			'next_text'    => __('Trang ti???p','devvn'),
		) );
		if($total > 1) echo '</div>';
}
function div_wrapper($content) {
    $pattern = '~<iframe.*src=".*(youtube.com|youtu.be).*</iframe>|<embed.*</embed>~'; //only iframe youtube
    preg_match_all($pattern, $content, $matches);
    foreach ($matches[0] as $match) { 
        $wrappedframe = '<div class="videoWrapper">' . $match . '</div>';
        $content = str_replace($match, $wrappedframe, $content);
    }
    return $content;    
}
add_filter('the_content', 'div_wrapper');

function get_thumbnail($img_size = 'thumbnail', $w = '', $h = ''){
	global $post;
	$url_thumb_full = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );  		
  	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $img_size );
	$url_thumb = $thumb['0'];
	$w_thumb = $thumb['1'];
	$h_thumb = $thumb['2'];
	if(($w_thumb != $w || $h_thumb != $h) && $url_thumb_full && $w != "" && $h != "") $url_thumb = aq_resize($url_thumb_full,$w,$h,true,true,true);
	if(!$url_thumb) $url_thumb = TEMP_URL.'/images/no-image-featured-image.png';
	return $url_thumb;
}

function get_excerpt($limit = 130){
	$excerpt = get_the_excerpt();
	if(!$excerpt) $excerpt = get_the_content();
	$excerpt = preg_replace(" (\[.*?\])",'',$excerpt);
	$excerpt = strip_shortcodes($excerpt);
	$excerpt = strip_tags($excerpt);
	$excerpt = substr($excerpt, 0, $limit);
	$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
	$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
	if($excerpt){
		$permalink = get_the_permalink();
		$excerpt = $excerpt.'... <a href="'.$permalink.'" title="" rel="nofollow">'.__('Xem th??m','devvn').'</a>';
	}
	return $excerpt;
}

function devvn_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'devvn_excerpt_more' );

function devvn_custom_excerpt_length( $length ) {
    return 34;
}
add_filter( 'excerpt_length', 'devvn_custom_excerpt_length', 999 );

if ( ! function_exists( 'devvn_ilc_mce_buttons' ) ) {
    function devvn_ilc_mce_buttons($buttons){
        array_push($buttons,
            "alignjustify",
            "subscript",
            "superscript"
        );
        return $buttons;
    }
    add_filter("mce_buttons", "devvn_ilc_mce_buttons");
}
if ( ! function_exists( 'devvn_ilc_mce_buttons_2' ) ) {
    function devvn_ilc_mce_buttons_2($buttons){
        array_push($buttons,
            "backcolor",
            "anchor",
            "fontselect",
            "fontsizeselect",
            "cleanup"
        );
        return $buttons;
    }
    add_filter("mce_buttons_2", "devvn_ilc_mce_buttons_2");
}
// Customize mce editor font sizes
if ( ! function_exists( 'devvn_mce_text_sizes' ) ) {
    function devvn_mce_text_sizes( $initArray ){
        $initArray['fontsize_formats'] = "9px 10px 12px 13px 14px 16px 17px 18px 19px 20px 21px 24px 28px 32px 36px";
        return $initArray;
    }
    add_filter( 'tiny_mce_before_init', 'devvn_mce_text_sizes' );
}

add_action( 'tgmpa_register', 'devvn_register_required_plugins' );
function devvn_register_required_plugins() {
	$plugins = array(
		array(
			'name'               => 'Advanced Custom Fields PRO',
			'slug'               => 'advanced-custom-fields-pro',
			'source'             => get_template_directory() . '/inc/plugins/advanced-custom-fields-pro.zip',
			'required'           => true,
			'force_activation'   => true,
			'force_deactivation' => true
		)
	);
	$config = array(
		'id'           => 'devvn',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);
	tgmpa( $plugins, $config );
}