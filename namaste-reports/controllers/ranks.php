<?php
class NamasteRepRankings {
	static function main() {
		global $wpdb;
		$_rank = new NamasteRepRank();

		// select courses
		$courses = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='namaste_course' AND post_status='publish' ORDER BY post_title");
		
		// select lessons 
		$lessons = $wpdb->get_results("SELECT tL.*, tM.meta_value as namaste_course FROM {$wpdb->posts} tL JOIN {$wpdb->postmeta} tM
			ON tM.meta_key = 'namaste_course' AND tM.post_id=tL.ID
			WHERE post_type='namaste_lesson' AND post_status='publish' ORDER BY post_title");
		
		// match lessons to courses
		foreach($courses as $cnt=>$course) {
			$course_lessons = array();

			foreach($lessons as $lesson) {
				if($lesson->namaste_course == $course->ID) {					
					$course_lessons[] = $lesson;
				}
			}
			
			$courses[$cnt]->lessons = $course_lessons;
		} // end foreach course
	
		// run some ranking. Defaults to number of courses completed in all courses		
		$students = self::rank($_POST);
		
		// display of the extra dropdowns
		$coursedd_display = $lessondd_display = 'none';
		if(!empty($_POST['rank_by'])) {
			if($_POST['rank_by'] == 'lessons' or $_POST['rank_by'] == 'pageviews' or $_POST['rank_by'] == 'avg_grade') $coursedd_display = 'inline';
			if($_POST['rank_by'] == 'pageviews' or $_POST['rank_by'] == 'avg_grade') $lessondd_display = 'inline';			
		}
		
		// when course is selected prepopulate the lessons
		if(!empty($_POST['course_id'])) {
			$lessons = array();
			foreach($courses as $course) {
				if($course->ID != $_POST['course_id']) continue;
				$lessons = $course->lessons;
			}
		}
		
		$rank_by = empty($_POST['rank_by']) ? 'courses' : $_POST['rank_by'];
		if(empty($_POST['num'])) $_POST['num'] = 10;
		
		include(NAMASTEREP_PATH."/views/users-rankings.html.php");
	} // end main()
	
	// actually decides what ranking to show depending on $_POST or shortcode data
	// when no course is selected rankings are: most courses completed, most lessons completed, most pageviews, most points, best avg grade per course
	// when course but no lesson is selected: most lessons completed, most pageviews, most points in the course, best avg grade per lesson
	// when lesson is selected: points, grade for the lesson, pageviews
	// $atts[num] is the number of students to include in the ranking
	static function rank($atts) {
		$students = array();	
		$_rank = new NamasteRepRank();
		if(empty($atts['num'])) $atts['num'] = 10;
		
		switch(@$atts['rank_by']) {
			case 'avg_grade':
				$students = $_rank->avg_grade($atts['num'], @$atts['course_id'], @$atts['lesson_id']);
			break;
			case 'points':
				$students = $_rank->points($atts['num']);
			break;
			case 'pageviews':
				$students = $_rank->pageviews($atts['num'], @$atts['course_id'], @$atts['lesson_id']);
			break;
			case 'lessons':
				$students = $_rank->lessons_completed($atts['num'], @$atts['course_id']);
			break;
			default:
				$students = $_rank->courses_completed($atts['num']);
			break;
		} // end switch	
	
		return $students;
	}
}