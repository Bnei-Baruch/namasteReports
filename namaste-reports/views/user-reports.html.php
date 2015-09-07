<style type="text/css">
.bold-action {
	font-weight:bold;
}

table.namasterep-table td {
	padding:8px;
}
</style>

<div class="wrap">
	<h2 align="center"><?php printf(__("%s's Global LMS Reports", 'namasterep'), $user->user_nicename)?></h2>
	
	<table align="center" width="70%" class="namasterep-table">
		<tr><th><?php _e('Pageviews per course <br> (Includes lesson pageviews)', 'namasterep')?></th><th><?php _e('% Completeness Per Course', 'namasterep')?></th></tr>
		
		<tr>
			<td align="center">
				<span class="pie"><?php foreach($courses as $cnt=>$course):
				if($cnt >0) echo ",";
				echo $course->visits;
				endforeach;?></span>
				<p>&nbsp;</p>	
				<table class="namasterep-table">
					<?php foreach($courses as $course):?>
						<tr><td width="20" bgcolor="<?php echo $course->color?>">&nbsp;</td><td><?php echo $course->post_title?></td><td><?php echo $course->visits?></td></tr>
					<?php endforeach;?>
				</table>			
			</td>
			<td align="center">
				<span class="bar"><?php foreach($courses as $cnt=>$course):
				if($cnt >0) echo ",";
				echo $course->completeness;
				endforeach;?></span>
				<p>&nbsp;</p>	
				<table class="namasterep-table">
					<?php foreach($courses as $course):?>
						<tr><td width="20" bgcolor="<?php echo $course->color?>">&nbsp;</td><td><?php echo $course->post_title?></td><td><?php echo $course->completeness?>%</td></tr>
					<?php endforeach;?>
				</table>			
			</td>
		</tr>
	</table>
	
	<h3 align="center"><a href="#" onclick="jQuery('#namasterepUserHistory').toggle('slow');return false;"><?php _e('View history of user actions', 'namasterep')?></a></h3>
	
	<div id="namasterepUserHistory" style="margin:auto;width:80%;display:none;">
		<?php if(sizeof($actions)):?>
			<table class="widefat">
				<tr><td colspan="2" id="nmrpActionLinks"><?php _e('Filter by action:', 'namasterep')?> 
				<a href="#" onclick="namasterepFilterByAction('all', this);return false;"><?php _e('[all actions]', 'namasterep')?></a> 
				<a href="#" onclick="namasterepFilterByAction('awarded_points', this);return false;"><?php _e('[earned points]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('started_lesson', this);return false;"><?php _e('[started lesson]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('completed_lesson', this);return false;"><?php _e('[completed lesson]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('enrolled_course', this);return false;"><?php _e('[enrolled course]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('completed_course', this);return false;"><?php _e('[completed course]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('submitted_solution', this);return false;"><?php _e('[submitted solution]', 'namasterep')?></a>
				<a href="#" onclick="namasterepFilterByAction('solution_processed', this);return false;"><?php _e('[solution approved/rejected]', 'namasterep')?></a></td></tr>
				<tr><th><?php _e('Date/time', 'namasterep');?></th><th><?php _e('What happened', 'namasterep')?></th></tr>
				<?php foreach($actions as $action):?>
					<tr class="namasterep-action <?php echo $action->action?>"><td><?php echo date($date_format.' '.$time_format, strtotime($action->datetime))?></td><td><?php echo $action->value;?></td></tr>
				<?php endforeach;?>			
			</table>
		<?php else:?>
			<p><?php _e('This user has not been active recently.', 'namasterep')?></p>
		<?php endif;?>
	</div>
	
	<p>&nbsp;</p>
	<hr>

	<!-- now reports for each course -->
	<?php foreach($courses as $course):?>
	<div class="wrap" style="clear:both;width:80%;margin:auto;">
		<h2 align="center"><?php printf(__('Reports for Course "%s"', 'namasterep'), $course->post_title)?></h2>
		<hr>
		
			<div style='float:left;width:200px;'>
				<h3><?php _e('Pageviews per lesson', 'namasterep');?></h3>
				
				<span class="lessons-pie"><?php foreach($course->lessons as $cnt=>$lesson):
				if($cnt >0) echo ",";
				echo $lesson->visits;
				endforeach;?></span>
				<p>&nbsp;</p>
				<table class="namasterep-table">
					<?php foreach($course->lessons as $cnt=>$lesson):?>
						<tr><td width="20" bgcolor="<?php echo $courses[$cnt]->color?>">&nbsp;</td><td><?php echo $lesson->post_title?></td><td><?php echo $lesson->visits?></td></tr>
					<?php endforeach;?>
				</table>			
			</div>
			<div style="float:left;">
				<h3><?php _e('Details per lesson', 'namasterep')?></h3>
				<table class="widefat">
					<tr><th width="250"><?php _e('Lesson', 'namasterep');?></th><th><?php _e('Pageviews', 'namasterep');?></th><th><?php _e('Solutions to assignments', 'namasterep');?></th><th><?php _e('% Approved solutions', 'namasterep');?></th><th><?php _e('% Completeness', 'namasterep');?></th>
					<?php if($use_grading_system):?><th><?php _e('Grade', 'namasterep');?></th><?php endif;?></tr>
					<?php foreach($course->lessons as $lesson):?>
						<tr><td><?php echo $lesson->post_title?></td><td><?php echo $lesson->visits?></td><td><?php echo $lesson->num_solutions?></td>
						<td><?php echo $lesson->percent_accepted_solutions?>%</td><td><?php echo $lesson->completeness?>%</td>
						<?php if($use_grading_system):?><th><?php echo $lesson->grade ? $lesson->grade : __('None yet', 'namasterep')?></th><?php endif;?></tr>
					<?php endforeach;?>
				</table>			
				
			</div>
			
	</div>
	<p>&nbsp;</p>
		
	<?php endforeach; // end foreach course ?>
</div>

<script type="text/javascript" >
jQuery(function() {
	jQuery("span.pie").peity("pie", {
		colours: [<?php foreach($courses as $cnt=>$course):
				if($cnt >0) echo ',';
				echo '"'.$course->color.'"';
				endforeach;?>],
		radius: 120
	});
	jQuery(".bar").peity("bar", {
		colours: [<?php foreach($courses as $cnt=>$course):
				if($cnt >0) echo ',';
				echo '"'.$course->color.'"';
				endforeach;?>],
		max: 100,
		width: 200,
		height: 160
	});
	jQuery("span.lessons-pie").peity("pie", {
		colours: [<?php foreach($courses as $cnt=>$course):
				if($cnt >0) echo ',';
				echo '"'.$course->color.'"';
				endforeach;?>],
		radius: 80
	});
});	
	
function namasterepFilterByAction(act, lnk) {
	jQuery('#nmrpActionLinks a').removeClass('bold-action');
	if(act == 'all') {
		jQuery('.namasterep-action').show();		
	}
	else {
		jQuery('.namasterep-action').hide();
		jQuery('.'+act).show();
		jQuery(lnk).addClass('bold-action');
	}
}
</script>