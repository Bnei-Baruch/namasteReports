<?php
/*
Plugin Name: Namaste! Reports
Plugin URI: http://namaste-lms.org/modules.php
Description: Advanced reports plugin for Namaste! LMS
Author: Kiboko Labs
Version: 0.6.5
Author URI: http://calendarscripts.info/
License: GPLv2 or later
*/

define( 'NAMASTEREP_PATH', dirname( __FILE__ ) );
define( 'NAMASTEREP_PARENT_DIR', dirname( dirname(__FILE__) ));
define( 'NAMASTEREP_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'NAMASTEREP_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
include_once(NAMASTEREP_PATH."/helpers/main.php");
include_once(NAMASTEREP_PATH."/models/basic.php");
include_once(NAMASTEREP_PATH."/models/lesson-report.php");
include_once(NAMASTEREP_PATH."/models/rank.php");
include_once(NAMASTEREP_PATH."/controllers/users.php");
include_once(NAMASTEREP_PATH."/controllers/shortcodes.php");
include_once(NAMASTEREP_PATH."/controllers/ranks.php");
include_once(NAMASTEREP_PATH."/controllers/courses.php");
include_once(NAMASTEREP_PATH."/models/course.php");

add_action('init', array("NamasteRep", "init"));

register_activation_hook(__FILE__, array("NamasteRep", "install"));
add_action('admin_enqueue_scripts', array("NamasteRep", "scripts"));

// other actions
add_action('wp_ajax_namastecon_ajax', 'namasterep_ajax');
add_action('wp_ajax_nopriv_namastecon_ajax', 'namasterep_ajax');

// add to menu
add_action('namaste_lms_admin_menu', array('NamasteRep', 'menu'));
add_action('namaste_lms_user_menu', array('NamasteRep', 'user_menu'));