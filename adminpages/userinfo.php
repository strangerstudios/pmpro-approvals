<?php
	global $wpdb, $current_user;

	//only admins can get this
if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'pmpro_approvals' ) ) {
	die( 'You do not have permission to perform this action.' );
}

if ( isset( $_REQUEST['l'] ) ) {
	$l = intval( $_REQUEST['l'] );
} else {
	// Default to a random level that the user has. Hopefully we never actually do this.
	$levels = pmpro_getMembershipLevelsForUser( $current_user->ID );
	if ( ! empty( $levels ) ) {
		$l = $levels[0]->id;
	} else {
		$l = 0;
	}
}

if ( ! empty( $_REQUEST['approve'] ) ) {
	PMPro_Approvals::approveMember( intval( $_REQUEST['approve'] ), $l );
} elseif ( ! empty( $_REQUEST['deny'] ) ) {
	PMPro_Approvals::denyMember( intval( $_REQUEST['deny'] ), $l );
} elseif ( ! empty( $_REQUEST['unapprove'] ) ) {
	PMPro_Approvals::resetMember( intval( $_REQUEST['unapprove'] ), $l );
}

	//get the user
if ( empty( $_REQUEST['user_id'] ) ) {
	wp_die( __( 'No user id passed in.', 'pmpro-approvals' ) );
} else {
	$user = get_userdata( intval( $_REQUEST['user_id'] ) );

	//user found?
	if ( empty( $user->ID ) ) {
		wp_die( sprintf( __( 'No user found with ID %d.', 'pmpro-approvals' ), intval( $_REQUEST['user_id'] ) ) );
	}
}
?>
<div class="wrap pmpro_admin">	
	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php echo intval( $user->ID ); ?> - <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_login ); ?>)
		<a href="<?php echo admin_url( 'user-edit.php?user_id=' . intval( $user->ID ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit Profile', 'pmpro-approvals' ); ?></a>
	</h2>	
	
	<h2><?php esc_html_e( 'Account Information', 'pmpro-approvals' ); ?></h2>
	<table class="form-table">
		<tr>
			<th><label><?php esc_html_e( 'User ID', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo intval( $user->ID ); ?></td>
		</tr>		
		<tr>
			<th><label><?php _e( 'Username', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $user->user_login ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Email', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo sanitize_email( $user->user_email ); ?></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Membership Level', 'pmpro-approvals' ); ?></label></th>
			<td>
			<?php
			//Changed this to show Membership Level Name now, so approvers don't need to go back and forth to see what level the user is applying for.
			 $level_details = pmpro_getSpecificMembershipLevelForUser( $user->ID, $l );

			 echo esc_html( $level_details->name );
        
			?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e( 'Approval Status', 'pmpro-approvals' ); ?></label></th>
			<td>
			<?php
			//show status here
			if ( PMPro_Approvals::isApproved( $user->ID, $l ) || PMPro_Approvals::isDenied( $user->ID, $l ) ) {
				if ( ! PMPro_Approvals::getEmailConfirmation( $user->ID ) ) {
					_e( 'Email Confirmation Required.', 'pmpro-approvals' );
				} else {
					echo PMPro_Approvals::getUserApprovalStatus( $user->ID, $l, false );
				?>
				[<a href="javascript:askfirst('Are you sure you want to reset approval for <?php echo esc_attr( $user->user_login ); ?>?', '?page=pmpro-approvals&user_id=<?php echo esc_attr( $user->ID ); ?>&unapprove=<?php echo esc_attr( $user->ID ); ?>&l=<?php echo esc_attr( $l ) ?>');">X</a>]
				<?php
				}   // end of email confirmation check.
			} else {
			?>
													
			<a href="?page=pmpro-approvals&user_id=<?php echo esc_attr( $user->ID ); ?>&approve=<?php echo esc_attr( $user->ID ); ?>&l=<?php echo esc_attr( $l ) ?>"><?php esc_html_e( 'Approve', 'pmpro-approvals' ); ?></a> |
			<a href="?page=pmpro-approvals&user_id=<?php echo esc_attr( $user->ID ); ?>&deny=<?php echo esc_attr( $user->ID ); ?>&l=<?php echo esc_attr( $l ) ?>"><?php esc_html_e( 'Deny', 'pmpro-approvals' ); ?></a>
			<?php
			}
			?>
			</td>
		</tr>
	</table>
	
	<?php
		if ( function_exists( 'pmpro_get_user_fields_for_profile' ) ) {
			global $pmpro_user_fields, $pmprorh_checkout_boxes;

			//show the fields
			if ( ! empty( $pmpro_user_fields ) ) {
				foreach ( $pmpro_user_fields as $where => $fields ) {
					$box = pmpro_get_field_group_by_name( $where );
					?>
					<?php if ( isset( $box->label ) ) { ?>
						<h2><?php echo esc_html( $box->label ); ?></h2>
					<?php } ?>

					<table class="form-table">
					<?php
					//cycle through groups

					foreach ( $fields as $field ) {
						// show field as long as it's not false
						if ( false != $field->profile ) {

						// Check to see if level is set for the field.
						if ( ! empty( $field->levels ) && ! in_array( $level_details->ID, $field->levels ) ) {
							continue;
						}
							
						?>
						<tr>
							<th><label><?php echo esc_attr( $field->label ); ?></label></th>
							<?php
							if ( is_array( get_user_meta( $user->ID, $field->name, true ) ) && 'file' === $field->type ) {
								$field = get_user_meta( $user->ID, $field->name, true );
								?>

								<td><a href="<?php echo esc_url( $field['fullurl'] ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'View File', 'pmpro-approvals' ); ?></a> (<?php echo esc_attr( $field['filename'] ); ?>)</td>


							<?php } else { 
								$user_field = get_user_meta( $user->ID, $field->name, true );

								// Get all array option values and break up the array into readable content.
								if ( is_array( $user_field ) ) {
									 $user_field_string = '';
									 foreach( $user_field as $key => $value ) {
										$user_field_string .= $value . ', ';
									}

									// remove trailing comma from string.
									echo '<td>' . esc_html( rtrim( $user_field_string, ', ' ) ) . '</td>';
								} else {
									// If Register Helper field is a valid URL, then let's make it clickable.
									if ( wp_http_validate_url( $user_field ) ) {
										echo '<td><a href="' . esc_url_raw( $user_field ) . '" target="_blank">' . esc_url( $user_field ) . '</a></td>';
									} else {
										echo '<td>' . esc_html( $user_field ) . '</td>';
									}
								}
							
 							} ?>
						</tr>
						<?php
						}   //endif
					}
					?>
					</table>
					<?php
				}
			}
		}
	?>
	<a href="?page=pmpro-approvals" class="">&laquo; <?php esc_html_e( 'Back to Approvals', 'pmpro-approvals' ); ?></a>
</div>
