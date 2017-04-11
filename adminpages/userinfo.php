<?php
	global $wpdb, $current_user;
	
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_approvals")))
	{		
		die("You do not have permission to perform this action.");
	}
	
	//get the user
	if(empty($_REQUEST['user_id']))
	{
		wp_die(__("No user id passed in.", 'pmpro-approvals'));
	}
	else
	{
		$user = get_userdata(intval($_REQUEST['user_id']));
		
		//user found?
		if(empty($user->ID))
		{
			wp_die(sprintf(__("No user found with ID %d.", 'pmpro-approvals'), intval($_REQUEST['user_id'])));
		}
		
		//if a chapter rep, make sure the user is in their chapter
		if(!current_user_can("edit_users"))
		{
			$rep_chapters = htcia_getRepChapters();
			
			if(empty($rep_chapters) || !in_array($user->chapter, $rep_chapters))
			{
				wp_die(__("This user is not in one of your chapters. You cannot view this information.", 'pmpro-approvals'));
			}
		}
	}
?>
<div class="wrap pmpro_admin">	
	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php echo $user->ID;?> - <?php echo $user->display_name;?> (<?php echo $user->user_login;?>)
	</h2>	
	
	<h3><?php _e('Account Information', 'pmpro-approvals');?></h3>
	<table class="form-table">
		<tr>
			<th><label><?php _e('User ID', 'pmpro-approvals');?>/label></th>
			<td><?php echo $user->ID;?></td>
		</tr>		
		<tr>
			<th><label><?php _e('Username', 'pmpro-approvals');?></label></th>
			<td><?php echo $user->user_login;?></td>
		</tr>
		<tr>
			<th><label><?php _e('Email', 'pmpro-approvals');?></label></th>
			<td><?php echo $user->user_email;?></td>
		</tr>
		<tr>
			<th><label><?php _e('Membership Status', 'pmpro-approvals');?></label></th>
			<td><?php //show status here or what is this even??></td>
		</tr>
		<tr>
			<th><label><?php _e('Approval Status', 'pmpro-approvals');?></label></th>
			<td><?php //show status here?></td>
		</tr>
	</table>
	
	<?php
		global $pmprorh_registration_fields, $pmprorh_checkout_boxes;
	
		//which fields are marked for the profile	
		$profile_fields = pmprorh_getProfileFields($user->ID, true);			
			
		//show the fields
		if(!empty($profile_fields))
		{			
			foreach($profile_fields as $where => $fields)
			{						
				$box = pmprorh_getCheckoutBoxByName($where);				
				?>
				<h3><?php echo $box->label;?></h3>
				<table class="form-table">
				<?php
				//cycle through groups						
				foreach($fields as $field)
				{				
				?>
				<tr>
					<th><label><?php echo $field->label;?></label></th>
					<td><?php echo get_usermeta($user->ID, $field->name, true);?></td>
				</tr>
				<?php				
				}
				?>
				</table>
				<?php
			}
		}			
	?>	
</div>