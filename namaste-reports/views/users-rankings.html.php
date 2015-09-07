<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a class='nav-tab' href='admin.php?page=namasterep'><?php _e('Main', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=users'><?php _e('User Reports', 'namasterep')?></a>
		<a class='nav-tab-active'><?php _e('User Rankings', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=courses'><?php _e('Course/Lesson Reports', 'namasterep')?></a>
	</h2>
	
	<h1><?php _e("How do users rank in the LMS", 'namasterep')?></h1>
	
	<form method="post">
		<p><?php _e('Show ranking by:', 'namasterep')?>
		<select name="rank_by" onchange="namasterepNankTypeChanged(this.value);">
			<option value="courses"><?php _e('Number of courses completed', 'namasterep')?></option>
			<option value="lessons" <?php if(!empty($_POST['rank_by']) and $_POST['rank_by'] == 'lessons') echo 'selected'?>><?php _e('Number of lessons completed', 'namasterep')?></option>
			<option value="pageviews" <?php if(!empty($_POST['rank_by']) and $_POST['rank_by'] == 'pageviews') echo 'selected'?>><?php _e('Pageviews', 'namasterep')?></option>
			<?php if(get_option('namaste_use_grading_system')):?>
				<option value="avg_grade" <?php if(!empty($_POST['rank_by']) and $_POST['rank_by'] == 'avg_grade') echo 'selected'?>><?php _e('Average grade', 'namasterep')?></option>
			<?php endif;?>
			<?php if(get_option('namaste_use_points_system')):?>
				<option value="points" <?php if(!empty($_POST['rank_by']) and $_POST['rank_by'] == 'points') echo 'selected'?>><?php _e('Points earned', 'namasterep')?></option>
			<?php endif;?>
		</select>
		
		<span id="nmrCourseSelector" style="display:<?php echo $coursedd_display?>;"><?php _e('In course:', 'namasterep')?> <select name="course_id" onchange="nmrCourseSelect(this.value);">
		<option value=""><?php _e('All Courses', 'namasterep')?>
		<?php foreach($courses as $course):?>
			<option value="<?php echo $course->ID?>" <?php if(!empty($_POST['course_id']) and $course->ID == $_POST['course_id']) echo 'selected'?>><?php echo $course->post_title?></option>
		<?php endforeach;?>
		</select></span>
		
		<span id="nmrLessonSelector" style="display:<?php echo $lessondd_display?>;"><?php _e('In lesson:', 'namasterep')?> <select name="lesson_id" id="nmrLessonID">
		<?php if(empty($_POST['course_id'])):?>
		<option value=""><?php _e('Select course', 'namasterep')?>
		<?php else: echo "<option value=''>".__('- All Lessons -', 'namasterep')."</option>";
			foreach($lessons as $lesson):?>
			<option value="<?php echo $lesson->ID?>" <?php if(@$_POST['lesson_id'] == $lesson->ID) echo 'selected'?>><?php echo $lesson->post_title?></option>
		<?php endforeach;
		endif;?>			
		</select></span>
		
		<?php _e('No. students to display:', 'namasterep');?> <input type="text" size="4" value="<?php echo empty($_POST['num']) ? 10 : $_POST['num']?>" name="num">
		
		<input type="submit" value="<?php _e('Run Reports', 'namasterep')?>"></p>
	</form>
	
	<h2><?php _e('User Rankings', 'namasterep')?></h2>
	
	<?php switch($rank_by) :
		case 'avg_grade': include(NAMASTEREP_PATH."/views/user-ranking-avg-grade.html.php"); break;
		case 'points': include(NAMASTEREP_PATH."/views/user-ranking-points.html.php"); break;	
		case 'pageviews': include(NAMASTEREP_PATH."/views/user-ranking-pageviews.html.php"); break;
		case 'lessons': include(NAMASTEREP_PATH."/views/user-ranking-lessons.html.php"); break;		
		case 'courses': include(NAMASTEREP_PATH."/views/user-ranking-courses.html.php"); break;
	endswitch;?>
</div>

<script type="text/javascript" >
// courses-lessons object
var nmrCourses = {
	<?php foreach($courses as $course): echo $course->ID?>: {
		<?php foreach($course->lessons as $lesson): ?><?php echo $lesson->ID.' : "'.$lesson->post_title.'"'?>, <?php endforeach;?>	
	},
	<?php endforeach;?>
};

function namasterepNankTypeChanged(val) {
	// hide course and lesson selectors
	jQuery('#nmrCourseSelector').hide();
	jQuery('#nmrLessonSelector').hide();
	// these stats can be per course
	if(val == 'lessons' || val == 'pageviews' || val == 'avg_grade') {
		jQuery('#nmrCourseSelector').show();
		if(val != 'lessons') jQuery('#nmrLessonSelector').show(); // and when not lessons, they can be per lesson
	}
}

function nmrCourseSelect(id) {
	if(id) {
		var lessons = nmrCourses[id];
		jQuery('#nmrLessonID').html('<option value=""><?php _e('- All Lessons -', 'namasterep')?></option>');
		jQuery.each(lessons, function(index, value){			
			jQuery('#nmrLessonID').append("<option value='"+ index +"'>" + value + "</option>"); 
		});
	}
}
</script>