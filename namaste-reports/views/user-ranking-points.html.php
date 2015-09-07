<?php if(!sizeof($students)):?>
	<p><?php _e('There are no students to rank.', 'namasterep')?></p>
<?php else:
	if(empty($in_shortcode)):?>
		<p><?php _e('Shortcode for this report:', 'namasterep')?> <input type="text" readonly value="[namaste-reports-rank points <?php echo $_POST['num']?>]" onclick="this.select();" size="30"></p>
	<?php endif;?>
	<table class="widefat namaste-reports">
		<tr><th><?php _e('Student name', 'namasterep');?></th><th><?php _e('Points', 'namasterep')?></th></tr>
		<?php foreach($students as $student):?>
			<tr><td><?php echo $student->user_nicename?></td><td><?php echo $student->points?></td></tr>
		<?php endforeach;?>
	</table>
<?php endif;?>	