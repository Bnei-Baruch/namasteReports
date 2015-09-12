<?php
// calculates user rankings
class NamasteRepRank {
	// ranks students by average grade
	// when no course is passed we get avg per course
	// when no lesson is passed we get avg per lesson
	// when course and lesson is passed, we just rank by grade in the lesson
	// homework grades are not used
	function avg_grade($num_users, $course_id = 0, $lesson_id = 0) {
		global $wpdb;
		
		// first we need to figure out how many gradepoints go to each grade. The system distributes gradepoints per grades
		$gradepoints = $this->get_gradepoints();
		
		// select all students along with their grades in the selected thing
		if($course_id == 0) {
			$users = $wpdb->get_results("SELECT tU.user_nicename as user_nicename, tU.ID as user_id, tSC.grade as grade
				FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.user_id = tU.ID
				AND tSC.status !='pending' AND tSC.status!='rejected' AND tSC.grade!='' ");
		}
		else {
			// course ID is given
			if($lesson_id == 0) {
				$lids = namasterep_get_lessons($course_id);
				$course_sql = " AND tSL.lesson_id IN (".implode(",", $lids).")";				
				
				$users = $wpdb->get_results("SELECT tU.user_nicename as user_nicename, tU.ID as user_id, tSL.grade as grade
				FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.student_id = tU.ID
				$course_sql
				WHERE tSL.grade!='' ");				
			}
			else {
				// lesson ID is given
				$users = $wpdb->get_results($wpdb->prepare("SELECT tU.user_nicename as user_nicename, tU.ID as user_id, tSL.grade as grade
				FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.student_id = tU.ID
				AND tSL.lesson_id=%d AND tSL.grade!='' ", $lesson_id));
			}
		}
		
		// we'll fill the users here along with their grade points and then sort with custom function
		$ranked_users = array();
		
		foreach($users as $user) {
			if($user->grade) $points = @$gradepoints[$user->grade];
			if(empty($points)) $points = 0;
			
			if(isset($ranked_users[$user->user_id])) {
				$ranked_users[$user->user_id]->gradepoints += $points;
				$ranked_users[$user->user_id]->num_grades++;
			} 
			else {
				$ranked_users[$user->user_id] = (object) array("id" => $user->user_id, "user_nicename" => $user->user_nicename, "gradepoints" => $points, "num_grades"=>1);
			}
		} // end foreach user
		
		// now calculate avg grade
		foreach($ranked_users as $cnt=>$user) {			
			$avg_grade = round( ($user->gradepoints / $user->num_grades), 2);
			$ranked_users[$cnt]->avg_grade = $avg_grade;
		}
		
		uasort($ranked_users, array($this, "compare_users"));		
		
		// slice the array to the required number
		$ranked_users = array_slice($ranked_users, 0, $num_users);				
		return $ranked_users;		
	} // end avg_grade method
	
	// small helper that assings points to grades based on their position
	// For example if there are 5 grades, the top one gets 5 points, the next one 4, then 3, 2, 1
	function get_gradepoints() {
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		$gradepoints = array();
		$total = sizeof($grades);
		
		$points = $total;
		foreach($grades as $grade) {
			$gradepoints[$grade] = $points;
			$points--;
		}
		
		return $gradepoints;
	}
	
	// this function outputs average grade based on avg gradepoints
	function output_avg_grade($avg_points) {
		$avg_points = round($avg_points);
		$gradepoints = $this->get_gradepoints();
		$gradepoints = array_flip($gradepoints);
		
		$value = @$gradepoints[$avg_points];
		if(!$value) $value = __('n/a','namasterep');
		return $value;
	}	
	
	// compares users by grade points
	private function compare_users($a, $b) {
		if ($a->avg_grade == $b->avg_grade) {
        return 0;
     }
     return ($a->avg_grade > $b->avg_grade) ? -1 : 1;
	}
	
	// rank users by points earned
	// in this version ranking can be only global
	// we'll need to add points field to student_lessons and student_courses tables in Namaste in order to allow more rankings
	function points($num_users, $course_id = 0, $lesson_id = 0) {
		global $wpdb;
		
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.ID as user_id, tU.user_nicename as user_nicename, tM.meta_value as points
		FROM {$wpdb->users} tU JOIN {$wpdb->usermeta} tM ON tM.user_id =tU.ID AND tM.meta_key = 'namaste_points'
		ORDER BY points DESC LIMIT %d", $num_users));
		
		return $users;
	}
	
	// rank users by number of courses completed
	function courses_completed($num_users) {
		global $wpdb;

		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.*, COUNT(tSC.id) as num_courses FROM {$wpdb->users} tU
			JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.user_id = tU.ID AND tSC.status='completed'
			GROUP BY tU.ID ORDER BY num_courses DESC LIMIT %d", $num_users));		
		
		return $users;
	}
	
	// by lessons NYI
	function lessons_completed($num_users, $course_id = 0) {
		global $wpdb;
		$course_sql = '';
		if(!empty($course_id)) {
			// select the lessons in that course
			$lids = namasterep_get_lessons($course_id);
			$course_sql = " AND tSL.lesson_id IN (".implode(",", $lids).")";			
		}
		
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.*, COUNT(tSL.id) as num_lessons FROM {$wpdb->users} tU
		JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.student_id = tU.ID AND tSL.status = 1 $course_sql
		GROUP BY tU.ID ORDER BY num_lessons DESC LIMIT %d", $num_users));
		
		return $users;
	}
	
	// by pageviews 
	function pageviews($num_users, $course_id = 0, $lesson_id = 0) {
		global $wpdb;
		$course_sql = $lesson_sql = "";
		if(!empty($course_id) and empty($lesson_id)) {
			// when showing course pageviews we also include pageviews of all lessons in the course
			$lids = namasterep_get_lessons($course_id);
			$course_sql = $wpdb->prepare("AND (course_id=%d OR lesson_id IN (".implode(',', $lids)."))", $course_id);
		}
		if(!empty($lesson_id)) {
			$course_sql = "AND course_id = 0"; // when pageview is on lesson, course_id = 0
			$lesson_sql = $wpdb->prepare("AND lesson_id=%d", $lesson_id);
		}
		
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.*, SUM(tV.visits) as pageviews FROM {$wpdb->users} tU
		JOIN ".NAMASTE_VISITS." tV ON tV.user_id = tU.ID $course_sql $lesson_sql
		GROUP BY tU.ID ORDER BY pageviews DESC LIMIT %d", $num_users));
	
		return $users;
	}
}