<?php
// shortcodes
class NamasteRepShortcodes {
	static function user_reports($atts) {
		global $user_ID;
		$user_id = empty($atts[0]) ? $user_ID : intval($atts[0]);
		
		NamasteRep::scripts();
		
		ob_start();
		NamasteRepUsers::reports($user_id);
		$content = ob_get_contents();
		return $content; 
	}
	
	static function user_ranks($atts) {
		$_rank = new NamasteRepRank();
		
		$rank_by = empty($atts[0]) ? 'courses' : $atts[0];
		$num = empty($atts[1]) ? 10 : $atts[1];
		$course_id = @$atts[2];
		$lesson_id = @$atts[3];
		
		$vars = array("rank_by"=>$rank_by, "num"=>$num, "course_id"=>$course_id, "lesson_id" => $lesson_id);
		$students = NamasteRepRankings :: rank($vars);
		$in_shortcode = true;
				
		ob_start();
		switch($rank_by) :
			case 'avg_grade': include(NAMASTEREP_PATH."/views/user-ranking-avg-grade.html.php"); break;
			case 'points': include(NAMASTEREP_PATH."/views/user-ranking-points.html.php"); break;	
			case 'pageviews': include(NAMASTEREP_PATH."/views/user-ranking-pageviews.html.php"); break;
			case 'lessons': include(NAMASTEREP_PATH."/views/user-ranking-lessons.html.php"); break;		
			case 'courses': include(NAMASTEREP_PATH."/views/user-ranking-courses.html.php"); break;
		endswitch;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	} 
}