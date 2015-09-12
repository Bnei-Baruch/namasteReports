<?php
// small helper to return all lesson IDs in a given course
function namasterep_get_lessons($course_id) {
	global $wpdb;
	
	$lessons = $wpdb->get_results($wpdb->prepare("SELECT tP.* FROM {$wpdb->posts} tP JOIN {$wpdb->postmeta} tM
		ON tM.meta_key = 'namaste_course' AND tM.meta_value=%d AND tM.post_id = tP.ID
		WHERE post_type = 'namaste_lesson' AND post_status='publish'", $course_id));
		$lids = array(0);
		foreach($lessons as $lesson) $lids[] = $lesson->ID;
	return $lids;		
}