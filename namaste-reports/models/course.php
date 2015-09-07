<?php
// course report model
class NamasteRepCourse {
	// returns total pageviews and pageviews per user
	function pageviews($course_id) {
		global $wpdb;
		
		$lids = namasterep_get_lessons($course_id);
		
		$visits = $wpdb->get_results($wpdb->prepare("SELECT SUM(visits) as visits FROM ".NAMASTE_VISITS." 
			WHERE (course_id=%d OR lesson_id IN(".implode(',', $lids).")) GROUP BY user_id", $course_id));
		if(empty($visits)) return array(0,0);	
		
		$total = 0;
		
		foreach($visits as $visit) {
			$total += $visit->visits;			
		}
		
		$avg = round($total / sizeof($visits), 2);
		
		return array($total, $avg);
	}
	
	// calculates average grade in the course
	function avg_grade($course_id) {
		global $wpdb;
		
		$_rank = new NamasteRepRank();				
		
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.user_nicename as user_nicename, tU.ID as user_id, tSC.grade as grade
		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.user_id = tU.ID
		AND tSC.course_id=%d
		WHERE tSC.grade!='' ", $course_id));		
		
		$gradepoints = $_rank->get_gradepoints();
		
		$total_points = 0;
		foreach($users as $user) {			
			if($user->grade) $points = @$gradepoints[$user->grade];
			if(empty($points)) $points = 0;			
			$total_points += $points;
		}	
		
		// avg points
		$avg_points = empty($total_points) ? 0 : round($total_points / sizeof($users));
		
		$gradepoints = array_flip($gradepoints);		
		$value = @$gradepoints[$avg_points];
		if(!$value) $value = __('n/a','namasterep');
		
		return $value;
	}
}