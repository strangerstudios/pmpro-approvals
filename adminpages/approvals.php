<?php
	global $wpdb, $current_user;
	
	//only admins can get this
	if ( ! function_exists( "current_user_can" ) || ( ! current_user_can( "manage_options" ) && ! current_user_can( "pmpro_approvals" ) ) ) {
		wp_die( __( "You do not have permissions to perform this action.", "pmproapp" ) );
	}	

	//vars
	if(isset($_REQUEST['s']))
		$s = sanitize_text_field($_REQUEST['s']);
	else
		$s = "";
	
	if(isset($_REQUEST['l']))
		$l = intval($_REQUEST['l']);
	else
		$l = false;
		
	//approving or denying?
	if(!empty($_REQUEST['approve'])) {
		PMPro_Approvals::approveMember(intval($_REQUEST['approve']), $l);		
	}
	elseif(!empty($_REQUEST['deny']))
	{
		//TODO: move this into a PMPro_Approvals class method, PMPro_Approvals::deny(...);
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals"))
		{						
			$msg = -1;
			$msgt = __("You do not have permission to preform approvals.", 'pmproapp');
		}
		else
		{
			$d_user_id = intval($_REQUEST['deny']);
						
			update_user_meta($d_user_id, "executive_approval", array("status"=>"denied", "timestamp"=>time(), "who" => $current_user->ID));
			
			//change status
			
			$msg = 1;
			$msgt = "Member was denied (executive).";		
		}
	}
	elseif(!empty($_REQUEST['unapprove']))
	{
		//TODO: move this into a PMPro_Approvals class method, PMPro_Approvals::unapprove(...);
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals"))
		{						
			$msg = -1;
			$msgt = __("You do not have permission to preform approvals.", 'pmproapp');
		}
		else
		{
			$d_user_id = intval($_REQUEST['unapprove']);
						
			delete_user_meta($d_user_id, "pmpro_approval");
			
			//change status
			
			$msg = 1;
			$msgt = __("Member has been unapproved.", 'pmproapp');		
		}
	}
	
	require_once( PMPRO_DIR . "/adminpages/admin_header.php" );
?>
	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php _e('Approvals', 'pmproapp');?>
	</h2>	
	<ul class="subsubsub">
		<li>			
			<?php _e('Show', 'pmproapp');?> <select name="l" onchange="jQuery('#posts-filter').submit();">
				<option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmproapp');?></option>
				<?php
					$levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
					//TODO: Only show levels that require approval
					foreach($levels as $level)
					{
				?>
					<option value="<?php echo $level->id?>" <?php if($l == $level->id) { ?>selected="selected"<?php } ?>><?php echo $level->name?></option>
				<?php
					}
				?>
			</select>			
		</li>
	</ul>
	<p class="search-box">
		<label class="hidden" for="post-search-input"><?php _e('Search Approvals', 'pmproapp');?>:</label>
		<input type="hidden" name="page" value="pmpro-approvals" />		
		<input id="post-search-input" type="text" value="<?php echo esc_attr($s);?>" name="s"/>
		<input class="button" type="submit" value="<?php _e('Search Approvals', 'pmproapp');?>"/>
	</p>
	<?php 
		//some vars for the search
		if(isset($_REQUEST['pn']))
			$pn = intval($_REQUEST['pn']);
		else
			$pn = 1;
			
		if(isset($_REQUEST['sortby']))		
			$sortby = $_REQUEST['sortby'];
		else
			$sortby = "user_registered";
			
		//make sure sortby is whitelisted
		$sortbys = array("pmpro_approval", "user_registered");
		if(!in_array($sortby, $sortbys))
			$sortby = "user_registered";
		
		if(!empty($_REQUEST['sortorder']) && $_REQUEST['sortorder'] == "ASC")
			$sortorder = "ASC";
		else
			$sortorder = "DESC";
			
		if(!empty($_REQUEST['limit']))
			$limit = intval($_REQUEST['limit']);
		else
			$limit = 15;
		
		$theusers = PMPro_Approvals::getApprovals($l, $s, $sortby, $sortorder, $pn, $limit);				
		$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");		
		
		if($theusers)
		{
		?>
		<p class="clear">
			<?php if($l == 1) { ?><?php echo $totalrows;?> <?php _e('applications awaiting review', 'pmproapp');?>.<?php } ?>
		</p>
		<?php
		}		
	?>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th><?php _e('ID', 'pmproapp');?></th>
				<th><?php _e('Username', 'pmproapp');?></th>
				<th><?php _e('Name', 'pmproapp');?></th>				
				<th><?php _e('Email', 'pmproapp');?></th>				
				<th><?php _e('Membership', 'pmproapp');?></th>					
				<th><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . esc_attr($s) . "&limit=" . $limit . "&pn=" . $pn . "&sortby=pmpro_approval");?><?php if($sortby == "pmpro_approval" && $sortorder == "DESC") { ?>&sortorder=ASC<?php } ?>"><?php _e('Approval', 'pmproapp');?></a></th>
				<th><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . esc_attr($s) . "&limit=" . $limit . "&pn=" . $pn . "&sortby=user_registered");?><?php if($sortby == "user_registered" && $sortorder == "DESC") { ?>&sortorder=ASC<?php } ?>"><?php _e('Joined', 'pmproapp');?></a></th>
			</tr>
		</thead>
		<tbody id="users" class="list:user user-list">	
			<?php	
				$count = 0;							
				foreach($theusers as $auser)
				{
					//get meta
					$theuser = get_userdata($auser->ID);						
					?>
						<tr <?php if($count++ % 2 == 0) { ?>class="alternate"<?php } ?>>
							<td><?php echo $theuser->ID?></td>
							<td class="username column-username">
								<?php echo get_avatar($theuser->ID, 32)?>								
								<?php if(current_user_can("edit_users")) { ?>
									<strong><a href="user-edit.php?user_id=<?php echo $theuser->ID?>"><?php echo $theuser->user_login?></a></strong>
								<?php } else { ?>
									<strong><a href="admin.php?page=pmpro-approvals&user_id=<?php echo $theuser->ID?>"><?php echo $theuser->user_login?></a></strong>
								<?php } ?>
								<br />
								<?php
									// Set up the hover actions for this user										
									$actions = apply_filters( 'pmpro_approvals_user_row_actions', array(), $theuser );
									$action_count = count( $actions );
									$i = 0;
									if($action_count)
									{
										$out = '<div class="row-actions">';
										foreach ( $actions as $action => $link ) {
											++$i;
											( $i == $action_count ) ? $sep = '' : $sep = ' | ';
											$out .= "<span class='$action'>$link$sep</span>";
										}
										$out .= '</div>';
										echo $out;
									}
								?>
							</td>
							<td><?php echo trim($theuser->first_name . " " . $theuser->last_name)?></td>							
							<td><a href="mailto:<?php echo $theuser->user_email?>"><?php echo $theuser->user_email?></a></td>							
							<td>
								<?php 
									if($auser->membership == "Pending")
									{
										if(!empty($theuser->pmpro_approval))
											echo "Approved";
										else
											echo "Pending Approval";
									}
									else
										echo $auser->membership
								?>
							</td>							
							<td>										
								<?php									
									if(!empty($theuser->pmpro_approval))
									{
										$approver_data = get_userdata($theuser->pmpro_approval['who']);
										$executive_approver_link = '<a href="'. get_edit_user_link( $approver_data->ID ) .'">'. esc_attr( $approver_data->display_name ) .'</a>';
										echo ucwords($theuser->pmpro_approval['status']) . " on " . date("m/d/Y", $theuser->pmpro_approval['timestamp'])." by ".$executive_approver_link;
										
										//link to unapprove
										?>
										[<a href="javascript:askfirst('Are you sure you want to unapprove <?php echo $theuser->user_login;?>?', '?page=pmpro-approvals&unapprove=<?php echo $theuser->ID;?>');">X</a>]
										<?php
									}
									else
									{										
									?>										
									<a href="?page=pmpro-approvals&s=<?php echo esc_attr($s);?>&limit=<?php echo intval($limit);?>&sortby=<?php echo $sortby;?>&sortorder=<?php echo $sortorder;?>&pn=<?php echo intval($pn);?>&approve=<?php echo $theuser->ID;?>">Approve</a> |
									<a href="?page=pmpro-approvals&s=<?php echo esc_attr($s);?>&limit=<?php echo intval($limit);?>&sortby=<?php echo $sortby;?>&sortorder=<?php echo $sortorder;?>&pn=<?php echo intval($pn);?>&deny=<?php echo $theuser->ID;?>">Deny</a>
									<?php
									}									
								?>
							</td>
							<td><?php echo date("m/d/Y", strtotime($theuser->user_registered))?></td>
														
						</tr>
					<?php
				}
				
				if(!$theusers)
				{
				?>
				<tr>
					<td colspan="9"><p><?php _e('No pending members found.', 'pmproapp');?></p></td>
				</tr>
				<?php
				}
			?>		
		</tbody>
	</table>
	</form>
	
	<?php
	echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, get_admin_url(NULL, "/admin.php?page=pmpro-approvals&s=" . urlencode($s)), "&limit=$limit&sortby=$sortby&sortorder=$sortorder&pn=");
	?>
	
<?php
	require_once( PMPRO_DIR . "/adminpages/admin_footer.php" );
?>
