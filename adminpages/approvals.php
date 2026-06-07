<?php
	global $wpdb, $current_user;

	//only admins can get this
if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'pmpro_approvals' ) ) {
	wp_die( __( 'You do not have permissions to perform this action.', 'pmpro-approvals' ) );
}

	//vars
if ( isset( $_REQUEST['s'] ) ) {
	$s = sanitize_text_field( $_REQUEST['s'] );
} else {
	$s = '';
}

if ( isset( $_REQUEST['l'] ) ) {
	$l = intval( $_REQUEST['l'] );
} else {
	$l = false;
}

if ( isset( $_REQUEST['status'] ) ) {
	$status = sanitize_text_field( $_REQUEST['status'] );
} else {
	$status = '';
}

	//make sure status is whitelisted
	$statuses = array( 'all', 'pending', 'approved', 'denied' );
if ( empty( $status ) || ! in_array( $status, $statuses ) ) {
	$status = 'all';
}

	//Approve, deny or reset member back to pending
if ( ! empty( $_REQUEST['approve'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	if ( ! PMPro_Approvals::isApproved( intval( $_REQUEST['approve'] ), $l ) ) {
		PMPro_Approvals::approveMember( intval( $_REQUEST['approve'] ), $l );
		$l = false;
	}
} elseif ( ! empty( $_REQUEST['deny'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	if ( ! PMPro_Approvals::isDenied( intval( $_REQUEST['deny'] ), $l ) ) {
		PMPro_Approvals::denyMember( intval( $_REQUEST['deny'] ), $l );
		$l = false;
	}
} elseif ( ! empty( $_REQUEST['unapprove'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	PMPro_Approvals::resetMember( intval( $_REQUEST['unapprove'] ), $l );
	$l = false;
}

	require_once PMPRO_DIR . '/adminpages/admin_header.php';

	//some vars for the search
	if ( isset( $_REQUEST['pn'] ) ) {
		$pn = intval( $_REQUEST['pn'] );
	} else {
		$pn = 1;
	}

	if ( isset( $_REQUEST['sortby'] ) ) {
		$sortby = $_REQUEST['sortby'];
	} else {
		$sortby = 'user_registered';
	}

	//make sure sortby is whitelisted
	$sortbys = array( 'pmpro_approval', 'user_registered' );
	if ( ! in_array( $sortby, $sortbys ) ) {
		$sortby = 'user_registered';
	}

	if ( ! empty( $_REQUEST['sortorder'] ) && $_REQUEST['sortorder'] == 'ASC' ) {
		$sortorder = 'ASC';
	} else {
		$sortorder = 'DESC';
	}

	if ( ! empty( $_REQUEST['limit'] ) ) {
		$limit = intval( $_REQUEST['limit'] );
	} else {
		$limit = 15;
	}

	$approval_users  = PMPro_Approvals::getApprovals( $l, $s, $status, $sortby, $sortorder, $pn, $limit );
	$totalrows = PMPro_Approvals::getApprovalCount( $status, $l, $s );
?>
<hr class="wp-header-end" />
<form id="posts-filter" method="get" action="">
	<h1 class="wp-heading-inline" style="margin-top: 0;"><?php esc_html_e( 'Approvals', 'pmpro-approvals' ); ?></h1>
	<input type="hidden" name="page" value="pmpro-approvals" />
	<?php if ( $approval_users ) { ?>
		<p class="search-box">
			<label class="hidden" for="post-search-input"><?php _e( 'Search Approvals', 'pmpro-approvals' ); ?>:</label>
			<input id="post-search-input" type="text" value="<?php echo esc_attr( $s ); ?>" name="s"/>
			<input class="button" type="submit" value="<?php _e( 'Search Approvals', 'pmpro-approvals' ); ?>"/>
		</p>
	<?php } ?>
	<p>
		<?php
			$approval_settings_link = '<a title="' . esc_attr__( 'Paid Memberships Pro - Approvals Add On Documentation', 'pmpro-approvals' ) . '" target="_blank" rel="nofollow noopener" href="https://www.paidmembershipspro.com/add-ons/approval-process-membership/?utm_source=plugin&utm_medium=pmpro-approvals&utm_campaign=add-ons">' . esc_html__( 'Approvals Add On', 'pmpro-approvals' ) . '</a>';
			// translators: %s: Link to Approvals Add On documentation.
			$approval_settings_text = sprintf( esc_html__( 'Learn more about using the %s.', 'pmpro-approvals' ), $approval_settings_link );
			echo wp_kses_post( $approval_settings_text );
		?>
	</p>
	<?php if ( $approval_users ) { ?>
		<div class="tablenav top">
			<div class="alignleft actions">
				<label class="screen-reader-text" for="filter-by-level"><?php esc_html_e( 'Filter by level', 'pmpro-approvals' ); ?></label>
				<select name="l" id="filter-by-level">
					<option value="0"><?php esc_html_e( 'All Levels', 'pmpro-approvals' ); ?></option>
					<?php
						$approval_level_ids = PMPro_Approvals::getApprovalLevels();
						if ( ! empty( $approval_level_ids ) ) {
							$levels = $wpdb->get_results( "SELECT id, name FROM $wpdb->pmpro_membership_levels WHERE id IN(" . implode( ',', $approval_level_ids ) . ') ORDER BY name' );
							foreach ( $levels as $level ) {
								?>
								<option value="<?php echo esc_attr( $level->id ); ?>" <?php selected( $l, $level->id ); ?>><?php echo esc_html( $level->name ); ?></option>
								<?php
							}
						}
					?>
				</select>
				<label class="screen-reader-text" for="filter-by-status"><?php esc_html_e( 'Filter by status', 'pmpro-approvals' ); ?></label>
				<select name="status" id="filter-by-status">
					<option value="all" <?php selected( $status, 'all' ); ?>><?php esc_html_e( 'All Statuses', 'pmpro-approvals' ); ?></option>
					<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'pmpro-approvals' ); ?></option>
					<option value="approved" <?php selected( $status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'pmpro-approvals' ); ?></option>
					<option value="denied" <?php selected( $status, 'denied' ); ?>><?php esc_html_e( 'Denied', 'pmpro-approvals' ); ?></option>
				</select>
				<?php submit_button( __( 'Filter', 'pmpro-approvals' ), '', 'filter_action', false ); ?>
			</div>
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo esc_html( sprintf( _n( '1 item', '%s items', $totalrows, 'pmpro-approvals' ), number_format_i18n( $totalrows ) ) ); ?></span>
			</div>
		</div>
	
		<table class="widefat striped pmpro_responsive_table">
			<thead>
				<tr class="thead">
					<th><?php esc_html_e( 'ID', 'pmpro-approvals' ); ?></th>
					<th><?php esc_html_e( 'Applicant', 'pmpro-approvals' ); ?></th>
					<th><?php esc_html_e( 'Email', 'pmpro-approvals' ); ?></th>
					<?php do_action( 'pmpro_approvals_list_extra_cols_header', $approval_users ); ?>
					<th><?php esc_html_e( 'Membership', 'pmpro-approvals' ); ?></th>
					<th><?php esc_html_e( 'Approval Status', 'pmpro-approvals' ); ?></th>
					<th><a href="<?php echo admin_url( 'admin.php?page=pmpro-approvals&s=' . esc_attr( $s ) . '&limit=' . $limit . '&pn=' . $pn . '&sortby=user_registered' ); ?>
											<?php
											if ( $sortby == 'user_registered' && $sortorder == 'DESC' ) {
							?>
							&sortorder=ASC<?php } ?>"><?php _e( 'Joined', 'pmpro-approvals' ); ?></a></th>				
				</tr>
			</thead>
			<tbody>	
			<?php
				foreach ( $approval_users as $approval_user ) {
					//get meta
					$user_data = get_userdata( $approval_user->ID );
					?>
					<tr>
						<td data-colname="<?php esc_attr_e( 'ID', 'pmpro-approvals' ); ?>"><?php echo $user_data->ID; ?></td>
						<td class="username column-username has-row-actions" data-colname="<?php esc_attr_e( 'Applicant', 'pmpro-approvals' ); ?>">
							<?php echo get_avatar( $user_data->ID, 32 ); ?>								
							<?php
								$pmpro_approval_view_url = add_query_arg(
									array(
										'page'    => 'pmpro-approvals',
										'user_id' => (int) $user_data->ID,
										'l'       => (int) $approval_user->membership_id,
									),
									admin_url( 'admin.php' )
								);
							?>
							<strong><a href="<?php echo esc_url( $pmpro_approval_view_url ); ?>"><?php echo esc_html( $user_data->display_name ); ?></a></strong>
							<?php
								// Set up the hover actions for this user.
								$pmpro_approvals_nonce      = wp_create_nonce( 'pmpro_approvals' );
								$pmpro_approval_action_base = add_query_arg(
									array(
										'page'                  => 'pmpro-approvals',
										's'                     => $s,
										'l'                     => (int) $approval_user->membership_id,
										'limit'                 => (int) $limit,
										'status'                => $status,
										'sortby'                => $sortby,
										'sortorder'             => $sortorder,
										'pn'                    => (int) $pn,
										'pmpro_approvals_nonce' => $pmpro_approvals_nonce,
									),
									admin_url( 'admin.php' )
								);

								$actions = array();

								$actions['pmpro_approvals_view'] = '<a href="' . esc_url( $pmpro_approval_view_url ) . '">' . esc_html__( 'View Application', 'pmpro-approvals' ) . '</a>';

								if ( PMPro_Approvals::isApproved( $user_data->ID, $approval_user->membership_id ) || PMPro_Approvals::isDenied( $user_data->ID, $approval_user->membership_id ) ) {
									$pmpro_approval_reset_url          = add_query_arg( 'unapprove', (int) $user_data->ID, $pmpro_approval_action_base );
									$actions['pmpro_approvals_reset'] = '<a href="javascript:askfirst(\'' . esc_js( sprintf( __( 'Are you sure you want to reset approval for %s?', 'pmpro-approvals' ), $user_data->user_login ) ) . '\', \'' . esc_js( $pmpro_approval_reset_url ) . '\');">' . esc_html__( 'Reset Approval', 'pmpro-approvals' ) . '</a>';
								} else {
									$actions['pmpro_approvals_approve'] = '<a href="' . esc_url( add_query_arg( 'approve', (int) $user_data->ID, $pmpro_approval_action_base ) ) . '">' . esc_html__( 'Approve', 'pmpro-approvals' ) . '</a>';
									$actions['pmpro_approvals_deny']    = '<a href="' . esc_url( add_query_arg( 'deny', (int) $user_data->ID, $pmpro_approval_action_base ) ) . '">' . esc_html__( 'Deny', 'pmpro-approvals' ) . '</a>';
								}

								if ( function_exists( 'pmpro_member_edit_get_panels' ) && function_exists( 'pmpro_get_edit_member_capability' ) && current_user_can( pmpro_get_edit_member_capability() ) ) {
									$actions['editmember'] = '<a href="' . esc_url( add_query_arg( array( 'page' => 'pmpro-member', 'user_id' => (int) $user_data->ID ), admin_url( 'admin.php' ) ) ) . '">' . esc_html__( 'Edit Member', 'pmpro-approvals' ) . '</a>';
								}
								if ( current_user_can( 'edit_users' ) ) {
									$actions['edituser'] = '<a href="' . esc_url( add_query_arg( array( 'user_id' => (int) $user_data->ID ), admin_url( 'user-edit.php' ) ) ) . '">' . esc_html__( 'Edit User', 'pmpro-approvals' ) . '</a>';
								}

								/**
								 * Filter the extra actions for this user in the approvals list table.
								 *
								 * @param array  $actions  The list of actions.
								 * @param object $template  The user data for this row.
								 * @param object $approval_user  The approval user data for this row.
								 */
								$actions = apply_filters( 'pmpro_approvals_user_row_actions', $actions, $user_data, $approval_user );

								$actions_html = [];
							?>
							<div class="row-actions">
								<?php
									foreach ( $actions as $action => $link ) {
										$actions_html[] = sprintf(
											'<span class="%1$s">%2$s</span>',
											esc_attr( $action ),
											$link
										);
									}
									if ( ! empty( $actions_html ) ) {
										echo implode( ' | ', $actions_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
								?>
							</div>
						</td>
						<td data-colname="<?php esc_attr_e( 'Email', 'pmpro-approvals' ); ?>">
							<?php echo esc_html( $user_data->user_email ); ?>
						</td>
						<?php do_action( 'pmpro_approvals_list_extra_cols_body', $user_data ); ?>						
						<td data-colname="<?php esc_attr_e( 'Membership', 'pmpro-approvals' ); ?>">
							<?php echo $approval_user->membership; ?>
						</td>						
						<td data-colname="<?php esc_attr_e( 'Approval Status', 'pmpro-approvals' ); ?>">										
							<?php
								if ( PMPro_Approvals::isApproved( $user_data->ID, $approval_user->membership_id ) ) {
									$pmpro_approval_badge_class = 'pmpro_tag-success';
									$pmpro_approval_badge_label = __( 'Approved', 'pmpro-approvals' );
								} elseif ( PMPro_Approvals::isDenied( $user_data->ID, $approval_user->membership_id ) ) {
									$pmpro_approval_badge_class = 'pmpro_tag-error';
									$pmpro_approval_badge_label = __( 'Denied', 'pmpro-approvals' );
								} else {
									$pmpro_approval_badge_class = 'pmpro_tag-alert';
									$pmpro_approval_badge_label = __( 'Pending', 'pmpro-approvals' );
								}
								echo '<span class="pmpro_tag ' . esc_attr( $pmpro_approval_badge_class ) . '">' . esc_html( $pmpro_approval_badge_label ) . '</span>';
							?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Joined', 'pmpro-approvals' ); ?>">
							<?php echo date_i18n( get_option( 'date_format' ), strtotime( $user_data->user_registered ) ); ?>
						</td>
					</tr>
					<?php
				}
			?>
			</tbody>
		</table>
		<?php
	} else {
		?>
		<p><?php esc_html_e( 'No members found.', 'pmpro-approvals' ); ?></p>
		<?php
	}
	?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<?php
				// Pagination
				echo wp_kses_post( 
					pmpro_getPaginationString(
						$pn,
						$totalrows,
						$limit,
						1,
						get_admin_url( null, '/admin.php?page=pmpro-approvals&s=' . urlencode( $s ) ),
						"&l=$l&limit=$limit&status=$status&sortby=$sortby&sortorder=$sortorder&pn=", 
						__( 'Approvals Pagination', 'pmpro-approvals' )
					)
				);
			?>
		</div>
	</div>
</form>
<?php
	require_once PMPRO_DIR . '/adminpages/admin_footer.php';
?>
