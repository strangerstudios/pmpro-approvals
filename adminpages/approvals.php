<?php
	global $wpdb, $current_user;
	
	//only admins can get this
	if ( ! function_exists( "current_user_can" ) || ! current_user_can( "pmpro_approvals" ) ) {
		wp_die( __( "You do not have permissions to perform this action.", 'pmpro-approvals' ) );
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
	
	if(isset($_REQUEST['status']))
		$status = sanitize_text_field($_REQUEST['status']);
	else
		$status = "";
	
	//make sure sortby is whitelisted
	$statuses = array("", "all", "pending", "approved", "denied");
	if(empty($status) || !in_array($status, $statuses))
		$status = "pending";
		
	//Approve, deny or reset member back to pending
	if(!empty($_REQUEST['approve'])) {
		check_admin_referer('pmpro_approvals', 'pmpro_approvals_nonce');
		PMPro_Approvals::approveMember(intval($_REQUEST['approve']), $l);		
	}
	elseif(!empty($_REQUEST['deny']))
	{
		check_admin_referer('pmpro_approvals', 'pmpro_approvals_nonce');
		PMPro_Approvals::denyMember(intval($_REQUEST['deny']), $l);
	}
	elseif(!empty($_REQUEST['unapprove']))
	{
		check_admin_referer('pmpro_approvals', 'pmpro_approvals_nonce');
		PMPro_Approvals::resetMember(intval($_REQUEST['unapprove']), $l);
	}
	
	require_once( PMPRO_DIR . "/adminpages/admin_header.php" );
?>
	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php _e('Approvals', 'pmpro-approvals');?>
	</h2>	
	<ul class="subsubsub">
		<li class="all"><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . urlencode($s) . "&l=$l&status=all") ?>" class="<?php if($status == 'all') {?>current<?php } ?>"><?php _e('All', 'pmpro-approvals');?></a></li> |
		<li class="pending"><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . urlencode($s) . "&l=$l&status=pending") ?>" class="<?php if($status == 'pending' || empty($status)) {?>current<?php } ?>"><?php _e('Pending', 'pmpro-approvals');?></a></li> |
		<li class="approved"><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . urlencode($s) . "&l=$l&status=approved") ?>" class="<?php if($status == 'approved') {?>current<?php } ?>"><?php _e('Approved', 'pmpro-approvals');?></a></li> |
		<li class="denied"><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . urlencode($s) . "&l=$l&status=denied") ?>" class="<?php if($status == 'denied') {?>current<?php } ?>"><?php _e('Denied', 'pmpro-approvals');?></a></li>			
	</ul>
	<p class="search-box">
		<label class="hidden" for="post-search-input"><?php _e('Search Approvals', 'pmpro-approvals');?>:</label>
		<input type="hidden" name="page" value="pmpro-approvals" />
		<input type="hidden" name="status" value="<?php echo esc_attr($status);?>" />		
		<input id="post-search-input" type="text" value="<?php echo esc_attr($s);?>" name="s"/>
		<input class="button" type="submit" value="<?php _e('Search Approvals', 'pmpro-approvals');?>"/>
	</p>
	<div class="tablenav top">	
		<?php _e('Show', 'pmpro-approvals');?> <select name="l" onchange="jQuery('#posts-filter').submit();">
		<option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmpro-approvals');?></option>
		<?php
			$approval_level_ids = PMPro_Approvals::getApprovalLevels();
			$levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels WHERE id IN(" . implode(',', $approval_level_ids) . ") ORDER BY name");					
			foreach($levels as $level)
			{
		?>
			<option value="<?php echo $level->id?>" <?php if($l == $level->id) { ?>selected="selected"<?php } ?>><?php echo $level->name?></option>
		<?php
			}
		?>
	</select>
	</div>
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
		
		$theusers = PMPro_Approvals::getApprovals($l, $s, $status, $sortby, $sortorder, $pn, $limit);				
		$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");		
		
		if($theusers)
		{
		?>
		<p class="clear">
			<?php if($l == 1) { ?><?php echo $totalrows;?> <?php _e('applications awaiting review', 'pmpro-approvals');?>.<?php } ?>
		</p>
		<?php
		}		
	?>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th><?php _e('ID', 'pmpro-approvals');?></th>
				<th><?php _e('Username', 'pmpro-approvals');?></th>
				<th><?php _e('Name', 'pmpro-approvals');?></th>				
				<th><?php _e('Email', 'pmpro-approvals');?></th>				
				<th><?php _e('Membership', 'pmpro-approvals');?></th>					
				<th><?php _e('Approval Status', 'pmpro-approvals');?></th>
				<th><a href="<?php echo admin_url("admin.php?page=pmpro-approvals&s=" . esc_attr($s) . "&limit=" . $limit . "&pn=" . $pn . "&sortby=user_registered");?><?php if($sortby == "user_registered" && $sortorder == "DESC") { ?>&sortorder=ASC<?php } ?>"><?php _e('Joined', 'pmpro-approvals');?></a></th>				
			</tr>
		</thead>
		<tbody id="users" class="list:user user-list">	
			<?php	
				$count = 0;							
				foreach($theusers as $auser)
				{
					//get meta
					$theuser = get_userdata($auser->ID);
					$theuser->membership_level = pmpro_getMembershipLevelForUser($theuser->ID);
					?>
						<tr <?php if($count++ % 2 == 0) { ?>class="alternate"<?php } ?>>
							<td><?php echo $theuser->ID?></td>
							<td class="username column-username">
								<?php echo get_avatar($theuser->ID, 32)?>								
								<?php if(current_user_can("edit_users") ) { ?>
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
									echo $auser->membership;
								?>
							</td>							
							<td>										
								<?php									
									$pmpro_approvals_nonce = wp_create_nonce('pmpro_approvals');
									if(PMPro_Approvals::isApproved($theuser->ID) || PMPro_Approvals::isDenied($theuser->ID)) {										
										echo PMPro_Approvals::getUserApprovalStatus($theuser->ID, $theuser->membership_level->id, false);
										
										//link to unapprove
										?>
										[<a href="javascript:askfirst('Are you sure you want to reset approval for <?php echo $theuser->user_login;?>?', '?page=pmpro-approvals&s=<?php echo esc_attr($s);?>&l=<?php echo $l;?>&limit=<?php echo intval($limit);?>&status=<?php echo $status;?>&sortby=<?php echo $sortby;?>&sortorder=<?php echo $sortorder;?>&pn=<?php echo intval($pn);?>&unapprove=<?php echo $theuser->ID;?>&pmpro_approvals_nonce=<?php echo urlencode($pmpro_approvals_nonce);?>');">X</a>]
										<?php									
									} else {
									?>										
									<a href="?page=pmpro-approvals&s=<?php echo esc_attr($s);?>&l=<?php echo $l;?>&limit=<?php echo intval($limit);?>&status=<?php echo $status;?>&sortby=<?php echo $sortby;?>&sortorder=<?php echo $sortorder;?>&pn=<?php echo intval($pn);?>&approve=<?php echo $theuser->ID;?>&pmpro_approvals_nonce=<?php echo urlencode($pmpro_approvals_nonce);?>">Approve</a> |
									<a href="?page=pmpro-approvals&s=<?php echo esc_attr($s);?>&l=<?php echo $l;?>&limit=<?php echo intval($limit);?>&status=<?php echo $status;?>&sortby=<?php echo $sortby;?>&sortorder=<?php echo $sortorder;?>&pn=<?php echo intval($pn);?>&deny=<?php echo $theuser->ID;?>&pmpro_approvals_nonce=<?php echo urlencode($pmpro_approvals_nonce);?>">Deny</a>
									<?php
									}
								?>
							</td>
							<td><?php echo date_i18n(get_option('date_format'), strtotime($theuser->user_registered))?></td>							
						</tr>
					<?php
				}
				
				if(!$theusers)
				{
				?>
				<tr>
					<td colspan="9"><p><?php _e('No pending members found.', 'pmpro-approvals');?></p></td>
				</tr>
				<?php
				}
			?>		
		</tbody>
	</table>
	</form>
	
	<?php
	echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, get_admin_url(NULL, "/admin.php?page=pmpro-approvals&s=" . urlencode($s)), "&l=$l&limit=$limit&status=$status&sortby=$sortby&sortorder=$sortorder&pn=");
	?>
	
<?php
	require_once( PMPRO_DIR . "/adminpages/admin_footer.php" );
?>
