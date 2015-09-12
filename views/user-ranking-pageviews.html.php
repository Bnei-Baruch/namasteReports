<?php if(!sizeof($students)):?>
	<p><?php _e('There are no students to rank.', 'namasterep')?></p>
<?php else:
	if(empty($in_shortcode)):?>
		<p><?php _e('Shortcode for this report:', 'namasterep')?> <input type="text" readonly value="[namaste-reports-rank pageviews <?php echo $_POST['num']?> <?php echo intval($_POST['course_id'])?> <?php echo intval($_POST['lesson_id'])?>]" onclick="this.select();" size="35"></p>
	<?php endif;?>
	<table class="widefat namaste-reports">
		<tr><th><?php _e('Student name', 'namasterep');?></th><th><?php _e('Pageviews', 'namasterep')?></th></tr>
		<?php foreach($students as $student):?>
			<tr><td><?php echo $student->user_nicename?></td><td><?php echo $student->pageviews?></td></tr>
		<?php endforeach;?>
	</table>
<?php endif;?>	