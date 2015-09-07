<?php 
// run reports per course/lesson
class NamasteRepCourses {
	static function main() {
		global $wpdb;
		$_courserep = new NamasteRepCourse();
		$_lessonrep = new NamasteRepLesson();
		
		// select all courses
		$courses = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='namaste_course' AND post_status='publish' ORDER BY post_title");
		
		if(!empty($_GET['course_id'])) {
			// select course
			$course = get_post($_GET['course_id']);
			
			// total and avg pageviews per user
			list($total_pageviews, $avg_pageviews) = $_courserep->pageviews($course->ID);
			
			// num students in the course
			$num_students = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tU.ID) FROM {$wpdb->users} tU
				JOIN ".NAMASTE_STUDENT_COURSES." tS ON tU.ID = tS.user_id 
				AND (tS.status = 'completed' OR tS.status = 'enrolled') AND tS.course_id=%d", $course->ID));  
			
			// avg % completeness
			$num_completed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tU.ID) FROM {$wpdb->users} tU
				JOIN ".NAMASTE_STUDENT_COURSES." tS ON tU.ID = tS.user_id 
				AND tS.status = 'completed' AND tS.course_id=%d", $course->ID));
			$percent_completed = empty($num_students) ? 0 : round(100 * $num_completed / $num_students);	  
			
			// avg grade if grading system is used
			$use_grading_system = get_option('namaste_use_grading_system');
			if($use_grading_system) {
				$avg_grade = $_courserep->avg_grade($course->ID);	
			}
			
			// select lessons
			$lessons = $wpdb->get_results($wpdb->prepare("SELECT tL.* FROM {$wpdb->posts} tL 
				JOIN {$wpdb->postmeta} tM ON tM.meta_key = 'namaste_course' AND tM.meta_value = %d AND tM.post_id = tL.ID
				WHERE tL.post_type='namaste_lesson' AND tL.post_status='publish' 
				ORDER BY tL.post_title", $course->ID));
			$lids = array(0);
			foreach($lessons as $lesson) $lids[] = $lesson->ID;
			
			// now select all studetns who have started any of the lessons
			$students = $wpdb->get_results("SELECT * FROM ".NAMASTE_STUDENT_LESSONS." WHERE lesson_id IN (".implode(',', $lids).")");
			
			// select all homeworks in these lessons
			$homeworks = $wpdb->get_results("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE lesson_id IN (".implode(',', $lids).")");
			$hids = array(0);
			foreach($homeworks as $homework) $hids[] = $homework->id; 
			
			// select all solutions to these homeworks
			$solutions = $wpdb->get_results("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE homework_id IN (".implode(',', $hids).")");
			
			// for each lesson select:
			// total pageviews & pageviews per student
			// % completed of all
			// avg grade
			// num homeworks & % completed			
			foreach($lessons as $cnt=>$lesson) {
				list($lesson_total_pageviews, $lesson_avg_pageviews) = $_lessonrep->pageviews($lesson->ID);
				$lessons[$cnt]->total_pageviews = $lesson_total_pageviews;
				$lessons[$cnt]->avg_pageviews = $lesson_avg_pageviews;
				
				$num_lesson_students = $num_lesson_completed = 0;
				
				foreach($students as $student) {
					if($student->lesson_id != $lesson->ID) continue;					
					$num_lesson_students++;
					if($student->status == 1) $num_lesson_completed++;
				} // end foreach student

				$percent_completed_lesson = empty($num_lesson_students) ? 0 : round( 100 * $num_lesson_completed / $num_lesson_students);				
				
				$lessons[$cnt]->num_students = $num_lesson_students;
				$lessons[$cnt]->num_completed = $num_lesson_completed;
				$lessons[$cnt]->percent_completed = $percent_completed_lesson;
				
				if($use_grading_system) {
					$lessons[$cnt]->avg_grade = $_lessonrep->avg_grade($lesson->ID);	
				}
				
				$num_homeworks = $num_solutions = $num_approved_solutions = 0;
				$lesson_homeworks = array();
				
				foreach($homeworks as $hct => $homework) {
					$homework_num_solutions = $homework_num_approved_solutions = $homework_num_students = 0;		
					$homework_uids = array();			
					
					if($homework->lesson_id != $lesson->ID) continue;
					$num_homeworks++;
					
					foreach($solutions as $solution) {
						if($solution->homework_id != $homework->id) continue;
						
						if(!in_array($solution->student_id, $homework_uids)) $homework_uids[] = $solution->student_id;
							
						$num_solutions++;
						$homework_num_solutions++;
						if($solution->status == 'approved') {
							$num_approved_solutions++;
							$homework_num_approved_solutions ++;
						}
					} // end foreach solution
					
					$homework_num_students = sizeof($homework_uids);
					$homeworks[$hct]->num_solutions = $homework_num_solutions;
					$homeworks[$hct]->num_approved_solutions = $homework_num_approved_solutions;
					$homeworks[$hct]->num_students = $homework_num_students;
					$homeworks[$hct]->num_per_student = empty($homework_num_students) ? 0 : round($homework_num_solutions / $homework_num_students);
					$homeworks[$hct]->percent_approved = empty($homework_num_solutions) ? 0 : round(100 * $homework_num_approved_solutions / $homework_num_solutions);
					
					$lesson_homeworks[] = $homeworks[$hct];
				} // end foreach homework
				
				$lessons[$cnt]->homeworks = $lesson_homeworks;
				$lessons[$cnt]->num_homeworks = $num_homeworks;
				$lessons[$cnt]->num_solutions = $num_solutions;
				$lessons[$cnt]->num_approved_solutions = $num_approved_solutions;
				$lessons[$cnt]->percent_approved_solutions = empty($num_solutions) ? 0 : round(100 * $num_approved_solutions / $num_solutions);
			} // end foreach lesson

			// prepare filters for studens query below
			$status_sql = '';
			if(!empty($_POST['status_filter'])) {
				$status_sql = $wpdb->prepare(" AND tSC.status = %s ", $_POST['status_filter']); 
				if(!empty($_POST['completed_clause'])) {
					$status_sql .= $wpdb->prepare(" AND tSC.enrollment_date >= tSC.completion_date - INTERVAL %d DAY ", $_POST['completed_filter']);
				}
			}
			
			// students in this course join to pageviews, num lessons strarted, num lessons completed, and grade
			$students = $wpdb->get_results($wpdb->prepare("SELECT tU.ID as student_id, tU.user_login as user_login, tU.display_name as display_name,
				tU.user_email as user_email, 
				tSC.enrollment_date as enrollment_date, tSC.completion_date as completion_date, tSC.grade as grade, tSC.status as status,
				(SELECT COUNT(tSL.id) FROM ".NAMASTE_STUDENT_LESSONS." tSL 
					WHERE tSL.student_id=tU.ID AND tSL.lesson_id IN (".implode(',', $lids).")) as lessons_started,
				(SELECT COUNT(tSL2.id) FROM ".NAMASTE_STUDENT_LESSONS." tSL2 
					WHERE tSL2.student_id=tU.ID AND tSL2.lesson_id IN (".implode(',', $lids).") AND tSL2.status=1) as lessons_completed
				FROM {$wpdb->users} tU 
				JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.user_id=tU.ID AND tSC.course_id=%d $status_sql				 
				ORDER BY tU.user_login", $course->ID, $course->ID));
		}		
		
		$dateformat = get_option('date_format');
		
		include(NAMASTEREP_PATH."/views/course-reports.html.php");
	}
}