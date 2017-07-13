<?php
	global $wpdb, $current_user;
	
	//only admins can get this
	if(!function_exists("current_user_can") || !current_user_can("pmpro_approvals"))
	{		
		die("You do not have permission to perform this action.");
	}

	if(isset($_REQUEST['l']))
		$l = intval($_REQUEST['l']);
	else
		$l = false;

	if(!empty($_REQUEST['approve'])) {
		PMPro_Approvals::approveMember(intval($_REQUEST['approve']), $l);		
	}
	elseif(!empty($_REQUEST['deny']))
	{
		PMPro_Approvals::denyMember(intval($_REQUEST['deny']), $l);
	}
	elseif(!empty($_REQUEST['unapprove']))
	{
		PMPro_Approvals::resetMember(intval($_REQUEST['unapprove']), $l);
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
			<th><label><?php _e('User ID', 'pmpro-approvals');?></label></th>
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
			<th><label><?php _e('Membership Level', 'pmpro-approvals');?></label></th>
			<td><?php //Changed this to show Membership Level Name now, so approvers don't need to go back and forth to see what level the user is applying for.
			 $level_details = pmpro_getMembershipLevelForUser( $user->ID );

			 echo $level_details->name;
			?></td>
		</tr>
		<tr>
			<th><label><?php _e('Approval Status', 'pmpro-approvals');?></label></th>
			<td><?php //show status here 
			if(PMPro_Approvals::isApproved($user->ID) || PMPro_Approvals::isDenied($user->ID)) {				
				echo PMPro_Approvals::getUserApprovalStatus($user->ID, NULL, false);
				?>
					[<a href="javascript:askfirst('Are you sure you want to reset approval for <?php echo $user->user_login;?>?', '?page=pmpro-approvals&user_id=<?php echo $user->ID; ?>&unapprove=<?php echo $user->ID;?>');">X</a>]
										<?php									
									} else {
									?>										
									<a href="?page=pmpro-approvals&user_id=<?php echo $user->ID ?>&approve=<?php echo $user->ID;?>">Approve</a> |
									<a href="?page=pmpro-approvals&user_id=<?php echo $user->ID ?>&deny=<?php echo $user->ID;?>">Deny</a>
									<?php
									}
			?></td>
		</tr>
	</table>
	
	<?php
		if(function_exists('pmprorh_getProfileFields')) {
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
						<td><?php echo get_user_meta($user->ID, $field->name, true);?></td>
					</tr>
					<?php				
					}
					?>
					</table>
					<?php
				}
			}
		}
	?>	
	<a href="?page=pmpro-approvals" class=""><?php _e('&laquo; Back to Approvals', 'pmpro-approvals');?></a>
</div>
