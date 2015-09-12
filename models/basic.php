<?php
// main model containing general config and UI functions
class NamasteRep {
   static function install() {
	   	global $wpdb;	
	   	$wpdb -> show_errors();
	   	self::init();
	   	
	   	// is Namaste! activated?
	   	$current_plugins = get_option('active_plugins');		
			// if(!in_array('namaste-lms/namaste.php', $current_plugins)) die("This plugin only works when Namaste! LMS is installed and active.");
			
			
   } // end install
   
   // main menu
   static function menu() {
   	$namaste_cap = current_user_can('namaste_manage')?'namaste_manage':'namaste';
   	add_submenu_page('namaste_options', __('Advanced Reports', 'namasterep'), __('Advanced Reports', 'namasterep'), 'namaste_manage', 'namasterep', array(__CLASS__, 'main'));
	}
	
	static function user_menu() {
		$namaste_cap = current_user_can('namaste_manage')?'namaste_manage':'namaste';
		
		// user reports page
   	if(get_option('namasterep_enable_user_reports')) {
   		add_submenu_page('namaste_my_courses', __('My Reports', 'namasterep'), __('My Reports', 'namasterep'), $namaste_cap, 'namasterep_my', array('NamasteRepUsers', 'my_reports'));
   	}
	}
	
	// CSS and JS
	static function scripts() {
		wp_register_script('jquery.peity', NAMASTEREP_URL."js/jquery.peity.min.js", false,	'1.2.0');
		wp_enqueue_script('jquery.peity');
	}
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'namasterep', false, NAMASTEREP_RELATIVE_PATH."/languages/" );
		if (!session_id()) @session_start();
		
		// reports column in the admin
		add_filter('manage_users_columns', array('NamasteRepUsers', 'add_reports_column'));
		add_action('manage_users_custom_column', array('NamasteRepUsers','manage_custom_column'), 10, 3); 
		
		// shortcodes
		add_shortcode('namaste-reports-user', array('NamasteRepShortcodes', 'user_reports'));
		add_shortcode('namaste-reports-rank', array('NamasteRepShortcodes', 'user_ranks'));
	}
	
	// main & help page + possibly some options
	static function main() {
		switch(@$_GET['action']) {
			case 'users':
				NamasteRepUsers :: reports();
			break;
			case 'rankings':
				NamasteRepRankings :: main();
			break;
			case 'courses':
				NamasteRepCourses :: main();
			break;
			default:
				// load the default page
				if(!empty($_POST['save_settings'])) {
					update_option('namasterep_enable_user_reports', $_POST['enable_user_reports']);
				}		
				
				include(NAMASTEREP_PATH."/views/main.html.php");
			break;
		}		
	} // end main()
}