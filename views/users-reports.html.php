<div class="wrap">
	<?php if($topnav):?>
	<h2 class="nav-tab-wrapper">
		<a class='nav-tab' href='admin.php?page=namasterep'><?php _e('Main', 'namasterep')?></a>
		<a class='nav-tab-active'><?php _e('User Reports', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=rankings'><?php _e('User Rankings', 'namasterep')?></a>
		<a class='nav-tab' href='admin.php?page=namasterep&action=courses'><?php _e('Course/Lesson Reports', 'namasterep')?></a>
	</h2>
	<?php endif;?>
	<?php if(empty($user_id)): // this is shown only from the admin page ?>
	<h1><?php _e('Namaste! LMS Advanced User Reports', 'namasterep')?></h1>
	
	<p><?php _e('Select user to view reports:', 'namasterep')?> <select name="user_id" onchange="window.location = 'admin.php?page=namasterep&action=users&user_id=' + this.value">
		<option value=""><?php _e('- please select -', 'namaserep')?></option>
		<?php foreach($users as $u):?>
			<option value="<?php echo $u->ID?>" <?php if(!empty($_GET['user_id']) and $_GET['user_id'] == $u->ID) echo 'selected'?>><?php echo $u->user_nicename?></option>
		<?php endforeach;?>
	</select></p>
	<?php endif;?>
	
	<?php if(!empty($_GET['user_id'])): 
	if(empty($user_id)) echo "<p>".__('To display this report on user-facing page use the shortcode','namasterep').' <b>[namaste-reports-user '.$_GET['user_id'].']</b></p>';
	include(NAMASTEREP_PATH."/views/user-reports.html.php"); endif;?>
</div>