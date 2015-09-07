<?php 
// user reports controller
class NamasteRepUsers {
	static function reports($user_id = 0, $topnav = true) {
		global $wpdb;
		if($user_id) $_GET['user_id'] = $user_id; // when calling from my_reports or shortcode. it has priority over $_GET['user_id']
		
		// get all users who have some courses assigned
		$users = $wpdb->get_results("SELECT tU.* FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tC
			ON tC.user_id=tU.ID AND tC.status!='rejected' AND status!='pending' 
			GROUP BY tU.ID ORDER BY tU.user_nicename");
		
		// if specific user is selected, select reports:
		// overal circle charts showing pageviews per course and % completeness per course - done
		// circle chart for each course showing pageviews on lessons		
		// table with lessons: visits, status, solutions to assignments, % accepted, grades if any, % completeness for the lesson
		// history of actions in the course
		
		// prepare 10 colors to use for the charts
		$colors = array('green', 'red', 'blue', 'yellow', 'orange', 'black', 'pink', 'brown', 'navy', 'maroon');
		$colorindex = 0;
		
		// This will probably be placed in another method to make it easier to output by shortcode and 
		// on user's own reports page
		if(!empty($_GET['user_id'])) {
			// select user
			$user = get_userdata($_GET['user_id']);
			$_lreport = new NamasteRepLesson();
			
			// select the courses he is in, along with all the visits in them
			$courses = $wpdb->get_results( $wpdb->prepare("SELECT tC.* FROM {$wpdb->posts} tC
				JOIN ".NAMASTE_STUDENT_COURSES." tS ON tS.course_id = tC.ID AND tS.user_id = %d 
				WHERE post_type='namaste_course' ORDER BY tC.post_title", $user->ID));
			$cids = array(0);
			foreach($courses as $course) $cids[] = $course->ID;
			
			// selects all lessons in the courses allong with visits
			$lessons = $wpdb->get_results( $wpdb->prepare("SELECT tL.*, tM.meta_value as course_id, tSL.status as lesson_status,
				tSL.grade as grade 
				FROM {$wpdb->posts} tL
				JOIN {$wpdb->postmeta} tM ON tM.meta_key = 'namaste_course' AND tM.meta_value IN (".implode(',', $cids).")
				AND tM.post_id = tL.ID
				LEFT JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.lesson_id = tL.ID AND tSL.student_id = %d
				WHERE tL.post_type = 'namaste_lesson' AND tL.post_status = 'publish' 
				ORDER BY tL.post_title", $user->ID));
				
			// select all visits of this user
			$visits = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".NAMASTE_VISITS." WHERE user_id=%d", $user->ID) );
			
			// select all homeworks in these courses
			$homeworks = $wpdb->get_results("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE course_id IN (".implode(',', $cids).")");
			$hids = array(0);
			foreach($homeworks as $homework) $hids[] = $homework->id;
			
			// select all solutions submitted by this user
			$solutions = $wpdb->get_results( $wpdb->prepare("SELECT tS.*, tH.lesson_id as lesson_id 
				FROM ".NAMASTE_STUDENT_HOMEWORKS." tS JOIN ".NAMASTE_HOMEWORKS." tH ON tH.id = tS.homework_id 
				WHERE tS.student_id=%d AND tS.homework_id IN (".implode(',', $hids).")", $user->ID)); 
			
			// match visits and % completeness in lessons
			foreach($lessons as $cnt=>$lesson) {
				$lesson_visits = 0;
				foreach($visits as $visit) {
					if($visit->lesson_id != $lesson->ID) continue;
					$lesson_visits += $visit->visits;
				}
				
				$lessons[$cnt]->completeness = $_lreport->completeness($lesson, $user->ID, $homeworks, $solutions);
				$lessons[$cnt]->visits = $lesson_visits;
				
				// add solutions
				$num_solutions = $accepted_solutions = 0;
				
				foreach($solutions as $solution) {
					if($solution->lesson_id == $lesson->ID) {
						$num_solutions++;
						if($solution->status == 'approved') $accepted_solutions++;
					}
				}
				
				if($num_solutions == 0) $percent_accepted_solutions = 0;
				else $percent_accepted_solutions = round(100 * ($accepted_solutions / $num_solutions) );
				
				$lessons[$cnt]->num_solutions = $num_solutions;
				$lessons[$cnt]->percent_accepted_solutions = $percent_accepted_solutions;
			} // end foreach lesson
			
			// now match lessons to courses, and visits
			foreach($courses as $cnt => $course) {
				$course_visits = $course_completeness = 0;
				$course_lessons = array();
				foreach($visits as $visit) {				
					if($visit->course_id == $course->ID) $course_visits += $visit->visits;					
				}
				
				// match the lessons and add the pageviews of each lesson
				foreach($lessons as $lesson) {
					if($lesson->course_id != $course->ID) continue;					
					$course_visits += $lesson->visits;
					$course_lessons[] = $lesson;
					$course_completeness += $lesson->completeness;
				}
				
				$courses[$cnt]->visits = $course_visits;
				$courses[$cnt]->lessons = $course_lessons;
				
				// % completeness of this course
				$courses[$cnt]->completeness = round( $course_completeness / sizeof($course_lessons));
				$courses[$cnt]->color = $colors[$colorindex];
				$colorindex++;
				if($colorindex >= 10) $colorindex = 0; // in case of too many courses, reset color index
			} // end foreach course
			
			// select actions history
			$actions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HISTORY." WHERE user_id=%d ORDER BY id DESC", $user->ID));
			
			$use_grading_system = get_option('namaste_use_grading_system');			
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			
		}	// end if user is selected	
		
		include(NAMASTEREP_PATH."/views/users-reports.html.php");
	}
	
	// add reports column to the Users page
	static function add_reports_column($columns) {	
		$columns['namasterep_reports'] = __('Namaste! Reports', 'namasterep');
	 	return $columns;	
	}
	
	static function manage_custom_column($empty='', $column_name, $id) {	  
	  if( $column_name == 'namasterep_reports' ) {
			return "<a href='admin.php?page=namasterep&action=users&user_id=".$id."' target='_blank'>".__('View reports', 'namasterep')."</a>";
	  }
	  
	  return $empty;
	}
	
	// show my own reports 
	static function my_reports() {
		global $user_ID;
		
		if(!is_user_logged_in()) wp_die("Not logged in.");
		
		self :: reports($user_ID, false); 
	}
}