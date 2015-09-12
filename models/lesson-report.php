<?php
// extracts lesson-related report data
class NamasteRepLesson {
	// completeness is calculated like this:
	// 100% when completed
	// 1 part for admin approval if there is, 1 part for exam, 2 parts for homeworks
	// note you must check $homeworksfor lesson_id and $solutions for homework_id as we may be passing 
	// an array of all users homeworks
	function completeness($lesson, $student_id, $homeworks, $solutions) {
		if($lesson->lesson_status == 1) return 100;
		
		$completeness = $completeness_parts = 0;
		
		$lesson_completion = get_post_meta($lesson->ID, 'namaste_completion', true);	
		if(!is_array($lesson_completion)) $lesson_completion = array();
		$required_homeworks = get_post_meta($lesson->ID, 'namaste_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		$required_exam = get_post_meta($lesson->ID, 'namaste_required_exam', true);
			
		if(in_array('admin_approval', $lesson_completion)) $completeness_parts += 1;
		if(!empty($required_exam)) $completeness_parts += 1;
		if(sizeof($required_homeworks)) $completeness_parts += 2;
		
		// when there are no requirements at all but the lesson is not completed, return 0
		if(!$completeness_parts) return 0;
		
		// if required exam, let's see whether it's completed
		if(NamasteLMSLessonModel::todo_exam($lesson->ID, $student_id, 'boolean')) $completeness += 1;
		
		// if required homeworks, let's see what part of them are completed so we can calculate part of 2
		if(sizeof($required_homeworks)) {
			$num_required = $num_completed = 0;
			
			foreach($homeworks as $homework) {
				if($homework->lesson_id == $lesson->ID and in_array($homework->id, $required_homeworks)) {
					$num_required++;					
					foreach($solutions as $solution) {
						if($solution->homework_id == $homework->id and $solution->status == 'approved') {
							$num_completed++;
							break;
						}	
				   } // end foreach solution
				} // end if match
			} // end foreach homework
			
			if($num_required) {
				$homework_completeness = round(($num_completed / $num_required) * 2, 2);
			}
			else $homework_completeness = 0;
			
			$completeness += $homework_completeness;
		} // end if sizeof homeworks
		
		// now calculate completeness in %
		$comp_perc = round(100 * ($completeness / $completeness_parts));
		
		return $comp_perc;
	} // end completeness()
	
	// total pageviews and avg pageviews per user
	function pageviews($lesson_id) {
		global $wpdb;
				
		$visits = $wpdb->get_results($wpdb->prepare("SELECT SUM(visits) as visits FROM ".NAMASTE_VISITS." 
			WHERE lesson_id=%d GROUP BY user_id", $lesson_id));
		if(empty($visits)) return array(0,0);	
		
		$total = 0;
		
		foreach($visits as $visit) {
			$total += $visit->visits;			
		}
		
		$avg = round($total / sizeof($visits), 2);
		
		return array($total, $avg);
	} // end pageviews()
	
	// calculates average grade in the lesson
	function avg_grade($lesson_id) {		
		global $wpdb;
		$_rank = new NamasteRepRank();				
		
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.user_nicename as user_nicename, tU.ID as user_id, tSL.grade as grade
		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.student_id = tU.ID
		AND tSL.lesson_id = %d
		WHERE tSL.grade!='' ", $lesson_id));		
		
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
	} // end avg_grade();
}