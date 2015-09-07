<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a class='nav-tab' href='admin.php?page=namasterep'><?php _e('Main', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=users'><?php _e('User Reports', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=rankings'><?php _e('User Rankings', 'namasterep')?></a>
		<a class='nav-tab-active'><?php _e('Course/Lesson Reports', 'namasterep')?></a>
	</h2>
	
	<h1><?php _e('Namaste! LMS Course Reports', 'namasterep');?></h1>
	
	<form method="get" action="admin.php">
	<input type="hidden" name="page" value="namasterep">
	<input type="hidden" name="action" value="courses">
		<p><?php _e('Select course:', 'namasterep')?> <select name="course_id" onchange="this.form.submit();">
			<option value=""><?php _e('- please select -', 'namasterep')?></option>
			<?php foreach($courses as $c):?>
				<option value="<?php echo $c->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$c->ID) echo 'selected'?>><?php echo $c->post_title?></option>
			<?php endforeach;?>		
		</select></p>	
	</form>
	
	<?php if(!empty($_GET['course_id'])):?>
		<h2><?php printf(__('Showing reports for course "%s"', 'namasterep'), $course->post_title)?></h2>
		
		<p><?php _e('Total all-time pageviews:', 'namsterep')?> <b><?php echo $total_pageviews?></b> <?php _e('(On the course page and the lesson pages in the course)','namasterep')?></p>
		<p><?php _e('Average pageviews per student:', 'namsterep')?> <b><?php echo $avg_pageviews?></b> </p>
		<p><?php _e('Students enrolled:', 'namasterep')?> <b><?php echo $num_students?></b></p>
		<p><?php _e('Students completed the course:', 'namasterep')?> <b><?php echo $num_completed?></b> - <?php echo $percent_completed.__('% of all', 'namasterep')?></p>
		<?php if($use_grading_system):?>
			<p><?php _e('Average grade achieved:', 'namasterep')?> <b><?php echo $avg_grade?></b></p>
		<?php endif;?>
		
		<h2><?php _e('Lessons In This Course', 'namasterep')?></h2>
		
		<table class="widefat">
			<tr><th><?php _e('Lesson Title', 'namasterep')?></th><th><?php _e('Total pageviews', 'namasterep')?></th>
			<th><?php _e('Avg. per user', 'namasterep')?></th><th><?php _e('Students started', 'namasterep')?></th>
			<th><?php _e('Students completed', 'namasterep')?></th>
			<?php if($use_grading_system):?><th><?php _e('Avg. grade', 'namasterep')?></th><?php endif;?>
			<th><?php _e('Num. assignments', 'namasterep')?></th><th><?php _e('Solutions submitted', 'namasterep')?></th>
			<th><?php _e('Approved solutions', 'namasterep')?></th></tr>
			<?php foreach($lessons as $lesson):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php echo $lesson->post_title?></td>
					<td><?php echo $lesson->total_pageviews?></td>
					<td><?php echo $lesson->avg_pageviews?></td>
					<td><?php echo $lesson->num_students?></td>
					<td><?php echo $lesson->num_completed.' ('.$lesson->percent_completed.'%)';?></td>
					<?php if($use_grading_system):?><td><?php echo $lesson->avg_grade?></td><?php endif;?>
					<td><?php echo $lesson->num_homeworks?>
						<?php if($lesson->num_homeworks):?><a href="#" onclick="jQuery('#homeworksRow<?php echo $lesson->ID?>').toggle('slow');return false;"><?php _e('[see details]', 'namasterep')?></a><?php endif;?></td>
					<td><?php echo $lesson->num_solutions?></td>
					<td><?php echo $lesson->num_approved_solutions.' ('.$lesson->percent_approved_solutions.'%)';?></td>
				</tr>
				<?php if($lesson->num_homeworks):?>
					<tr id="homeworksRow<?php echo $lesson->ID?>" style="display:none;"><td colspan="9" style="background-color:white;padding:5px 20px;">
						<h3><?php _e('Assignments in this lesson', 'namasterep')?></h3>
						
						<table class="widefat">
							<tr><th><?php _e('Assignment', 'namasterep')?></th><th><?php _e('Solutions submitted', 'namasterep')?></th>
							<th><?php _e('Approved solutions', 'namasterep')?></th><th><?php _e('Students who submitted', 'namasterep')?></th>
							<th><?php _e('Avg. solutions per student', 'namasterep')?></th></tr>
							<?php foreach($lesson->homeworks as $homework):?>
								<tr><td><?php echo $homework->title?></td><td><?php echo $homework->num_solutions?></td>
								<td><?php echo $homework->num_approved_solutions.' ('.$homework->percent_approved.'%)';?></td>
								<td><?php echo $homework->num_students?></td><td><?php echo $homework->num_per_student?></td></tr>
							<?php endforeach;?>
						</table>					
					</td></tr>
				<?php endif;?>	
				</td></tr>
			<?php endforeach;?>
		</table>
		
		<p>&nbsp;</p>
		<h2><?php _e('Students Enrolled In This Course', 'namasterep')?></h2>
		
		<form method="post">
			<p><?php _e('Show students with status:', 'namasterep');?> <select name="status_filter" onchange="this.value == 'completed' ? jQuery('#completedFilter').show() : jQuery('#completedFilter').hide();">
				<option value=""><?php _e('Any', 'namasterep');?></option>
				<option value="enrolled" <?php if(!empty($_POST['status_filter']) and $_POST['status_filter'] == 'enrolled') echo 'selected';?>><?php _e('In progress', 'namasterep');?></option>
				<option value="completed" <?php if(!empty($_POST['status_filter']) and $_POST['status_filter'] == 'completed') echo 'selected';?>><?php _e('Completed', 'namasterep');?></option>
			</select>
			<span id="completedFilter" style="display:<?php echo (empty($_POST['status_filter']) or $_POST['status_filter'] != 'completed') ? 'none' : 'inline';?>">
				<input type="checkbox" name="completed_clause" value="1" <?php if(!empty($_POST['completed_clause'])) echo 'checked'?>> 
				<?php printf(__('Completed the course within %s days of enrollment.', 'namasterep'), 
					'<input type="text" name="completed_filter" size="5" value="'.@$_POST['completed_filter'].'">');?>			
			</span>
			<input type="submit" value="<?php _e('Filter students', 'namasterep');?>"></p>
		</form>
		
		<table class="widefat">
			<tr><th><?php _e('Username / name', 'namasterep')?></th><th><?php _e('Enrollment date', 'namasterep')?></th>
			<th><?php _e('Status', 'namasterep')?></th><th><?php _e('Completion date', 'namasterep')?></th>
			<th><?php _e('Lessons started', 'namasterep')?></th>
			<th><?php _e('Lessons completed', 'namasterep')?></th>
			<?php if($use_grading_system):?><th><?php _e('Course grade', 'namasterep')?></th><?php endif;?>
			</tr>
			<?php foreach($students as $student):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php printf(__('%s (%s)', 'namasterep'), $student->user_login, stripslashes($student->display_name))?></td>
					<td><?php echo date('Y-m-d', strtotime($student->enrollment_date));?></td>
					<td><?php echo ($student->status == 'completed') ? __('Completed', 'namasterep') : __('In progress', 'namasterep');?></td>
					<td><?php echo ($student->status == 'completed') ? date('Y-m-d', strtotime($student->completion_date)) : __('N/a', 'namasterep');?></td>
					<td><?php echo $student->lessons_started;?></td>
					<td><?php echo $student->lessons_completed;?></td>
					<?php if($use_grading_system):?><td><?php echo $student->grade?></td><?php endif;?>					
				</tr>				
			<?php endforeach;?>
		</table>
	<?php else:?>
		<p><?php _e('Please select course to load reports.', 'namasterep')?></p>	
	<?php endif;?>
</div>