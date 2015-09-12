<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a class='nav-tab-active'><?php _e('Main', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=users'><?php _e('User Reports', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=rankings'><?php _e('User Rankings', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=courses'><?php _e('Course/Lesson Reports', 'namasterep')?></a>
	</h2>

	<form method="post">
	<h1><?php _e('Advanced Reports for Namaste! LMS', 'namasterep');?></h1>
	
	<p>This extra plugin adds advanced reports to your learning managemenet system. Here is how it can be used:</p>
	
	<h2><a href="admin.php?page=namasterep&action=users">User Reports</a></h2>
	
	<p>Go to the <a href="admin.php?page=namasterep&action=users">user reports</a> page and select user to view advanced reports for their performance. Alternatively while you are on the <a href="users.php">Manage users</a> page in your administration you can click on "Namaste! Reports" link for each user.</p>
	
	<p>You will be presented tables and charts which show which lessons they visit most, how well they perform on assignments, and more.</p>
	
	<h2><a href="admin.php?page=namasterep&action=rankings">User Rankings</a></h2>
	
	<p><a href="admin.php?page=namasterep&action=rankings">This page</a> will show you how your students rank for given course, lesson, or in the total courses. The rankings can be published using shortcodes.</p>
	
	<h2><a href="admin.php?page=namasterep&action=courses">Course and Lesson Reports</a></h2>
	
	<p>See which course and which lessons attract the most attention <a href="admin.php?page=namasterep&action=courses">on this page</a>. What is the percentage of completed course and lessons vs the visits on them. Which assignments are easier or harder. </p>
	
	<h2>Reports on The User-Facing Pages</h2>
	
	<p><input type="checkbox" name="enable_user_reports" value="1" <?php if(get_option('namasterep_enable_user_reports')) echo 'checked'?>> <?php _e('Enable "Advanced Reports" link in the user dashboard', 'namasterep');?> <input type="submit" name="save_settings" value="<?php _e('Save', 'namasterep')?>"</p>
	
	<p><?php _e('You can also show reports by using shortcodes. Use the shortcode', 'namasterep')?> <input type="text" readonly value="[namaste-reports-user]" size="20" onclick="this.select();"> <?php _e("to display user's own reports or the shortcode", 'namasterep')?> <input type="text" readonly value="[namaste-reports-user X]" size="20" onclick="this.select();"> <?php _e('to display reports of a given user (replace X with the user ID)', 'namasterep')?></p>
	</form>
</div>