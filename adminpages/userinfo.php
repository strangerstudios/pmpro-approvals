<?php
	global $wpdb, $current_user, $pmpro_user_fields;

	//only admins can get this
if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'pmpro_approvals' ) ) {
	wp_die( __( 'You do not have permissions to perform this action.', 'pmpro-approvals' ) );
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

	//process approve/deny/reset actions. The action methods set the global $msg/$msgt
	//that admin_header.php prints; on success, override $msgt to personalize it with the
	//member's name. On failure the methods set their own error message, so leave it alone.
	global $msg, $msgt;
if ( ! empty( $_REQUEST['approve'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	if ( PMPro_Approvals::approveMember( intval( $_REQUEST['approve'] ), $l ) ) {
		/* translators: %s: member display name */
		$msgt = sprintf( __( '%s has been approved.', 'pmpro-approvals' ), $user->display_name );
	}
} elseif ( ! empty( $_REQUEST['deny'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	if ( PMPro_Approvals::denyMember( intval( $_REQUEST['deny'] ), $l ) ) {
		/* translators: %s: member display name */
		$msgt = sprintf( __( '%s has been denied.', 'pmpro-approvals' ), $user->display_name );
	}
} elseif ( ! empty( $_REQUEST['unapprove'] ) ) {
	check_admin_referer( 'pmpro_approvals', 'pmpro_approvals_nonce' );
	if ( PMPro_Approvals::resetMember( intval( $_REQUEST['unapprove'] ), $l ) ) {
		/* translators: %s: member display name */
		$msgt = sprintf( __( 'Approval for %s has been reset to pending.', 'pmpro-approvals' ), $user->display_name );
	}
}

	//the level this user is being approved for
	$level_details = pmpro_getSpecificMembershipLevelForUser( $user->ID, $l );

	require_once PMPRO_DIR . '/adminpages/admin_header.php';
?>
<hr class="wp-header-end" />
<nav class="pmpro-nav-secondary pmpro-breadcrumbs" aria-labelledby="pmpro-approvals-breadcrumbs">
	<h2 id="pmpro-approvals-breadcrumbs" class="screen-reader-text"><?php esc_html_e( 'Approvals navigation', 'pmpro-approvals' ); ?></h2>
	<ul>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pmpro-approvals' ) ); ?>" title="<?php esc_attr_e( 'View All Approvals', 'pmpro-approvals' ); ?>">
				<?php esc_html_e( 'Approvals', 'pmpro-approvals' ); ?>
			</a>
		</li>
		<li>
			<span class="current" aria-current="page">
				<?php
					/* translators: 1: member display name, 2: user ID */
					echo esc_html( sprintf( __( '%1$s (ID #%2$d)', 'pmpro-approvals' ), $user->display_name, $user->ID ) );
				?>
			</span>
		</li>
	</ul>
</nav>

<h1><?php echo esc_html( sprintf( __( 'Application for %s', 'pmpro-approvals' ), $user->display_name ) ); ?></h1>

<div class="pmpro_two_col pmpro_two_col-right">

	<div class="pmpro_main">
		<div class="pmpro_section" data-visibility="shown" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
					<span class="dashicons dashicons-arrow-up-alt2"></span>
					<?php esc_html_e( 'Application Information', 'pmpro-approvals' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside">
				<table class="form-table">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Membership Level', 'pmpro-approvals' ); ?></label></th>
						<td><?php echo ! empty( $level_details ) ? esc_html( $level_details->name ) : esc_html__( '&#8212;', 'pmpro-approvals' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Approval Status', 'pmpro-approvals' ); ?></th>
						<td>
							<?php
								$pmpro_approvals_nonce = wp_create_nonce( 'pmpro_approvals' );
								$action_base           = add_query_arg(
									array(
										'page'                  => 'pmpro-approvals',
										'user_id'               => (int) $user->ID,
										'l'                     => (int) $l,
										'pmpro_approvals_nonce' => $pmpro_approvals_nonce,
									),
									admin_url( 'admin.php' )
								);

								if ( PMPro_Approvals::isApproved( $user->ID, $l ) || PMPro_Approvals::isDenied( $user->ID, $l ) ) { ?>
									<p><?php echo wp_kses_post( PMPro_Approvals::getUserApprovalStatus( $user->ID, $l, false ) ); ?></p>
									<p>
										<a href="javascript:askfirst('<?php echo esc_js( sprintf( __( 'Are you sure you want to reset approval for %s?', 'pmpro-approvals' ), $user->user_login ) ); ?>', '<?php echo esc_js( add_query_arg( 'unapprove', (int) $user->ID, $action_base ) ); ?>');" class="button button-secondary pmpro-has-icon pmpro-has-icon-image-rotate">
											<?php esc_html_e( 'Reset Approval', 'pmpro-approvals' ); ?>
										</a>
									</p>
									<?php
								} else {
									?>
									<p><?php echo wp_kses_post( PMPro_Approvals::getUserApprovalStatus( $user->ID, $l, false ) ); ?></p>
									<p>
										<a href="<?php echo esc_url( add_query_arg( 'approve', (int) $user->ID, $action_base ) ); ?>" class="button button-primary pmpro-has-icon pmpro-has-icon-yes">
											<?php esc_html_e( 'Approve', 'pmpro-approvals' ); ?>
										</a>
										<a href="<?php echo esc_url( add_query_arg( 'deny', (int) $user->ID, $action_base ) ); ?>" class="button is-destructive pmpro-has-icon pmpro-has-icon-no">
											<?php esc_html_e( 'Deny', 'pmpro-approvals' ); ?>
										</a>
									</p>
									<?php
								}
							?>
						</td>
					</tr>
					<?php if ( function_exists( 'pmproec_load_plugin_text_domain' ) ) { ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Email Confirmation', 'pmpro-approvals' ); ?></th>
							<td>
								<?php if ( PMPro_Approvals::getEmailConfirmation( $user->ID ) ) { ?>
									<span class="pmpro_tag pmpro_tag-success"><?php esc_html_e( 'Confirmed', 'pmpro-approvals' ); ?></span>
								<?php } else { ?>
									<span class="pmpro_tag pmpro_tag-alert"><?php esc_html_e( 'Not Confirmed', 'pmpro-approvals' ); ?></span>
									<p class="description"><?php esc_html_e( 'This user has not confirmed their email address. Membership access may be restricted.', 'pmpro-approvals' ); ?></p>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

		<?php
			// Show the user field groups read-only, using core's field value display.
		if ( function_exists( 'pmpro_get_user_fields_for_profile' ) && ! empty( $pmpro_user_fields ) ) {
			foreach ( $pmpro_user_fields as $where => $fields ) {
				$box = pmpro_get_field_group_by_name( $where );

				// Build the read-only rows first so we can skip empty groups.
				$fields_html = '';
				foreach ( $fields as $field ) {
					// Show field as long as it's not false.
					if ( false == $field->profile ) {
						continue;
					}

					// Check to see if level is set for the field.
					if ( ! empty( $field->levels ) && ! empty( $level_details ) && ! in_array( $level_details->ID, $field->levels ) ) {
						continue;
					}

					// Resolve the stored value the same way PMPro core does.
					$meta_key = ! empty( $field->meta_key ) ? $field->meta_key : $field->name;
					if ( metadata_exists( 'user', $user->ID, $meta_key ) ) {
						$value = get_user_meta( $user->ID, $meta_key, true );
					} elseif ( isset( $field->value ) ) {
						$value = $field->value;
					} else {
						$value = '';
					}

					// displayValue() returns the value rendered read-only (already escaped via wp_kses).
					$fields_html .= '<tr><th scope="row"><label>' . esc_html( $field->label ) . '</label></th><td>' . $field->displayValue( $value, false ) . '</td></tr>';
				}

				if ( empty( $fields_html ) ) {
					continue;
				}
				?>
				<div class="pmpro_section" data-visibility="shown" data-activated="true">
					<div class="pmpro_section_toggle">
						<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
							<span class="dashicons dashicons-arrow-up-alt2"></span>
							<?php echo esc_html( isset( $box->label ) ? $box->label : '' ); ?>
						</button>
					</div>
					<div class="pmpro_section_inside">
						<table class="form-table">
							<?php echo $fields_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</table>
					</div> <!-- end pmpro_section_inside -->
				</div> <!-- end pmpro_section -->
				<?php
			}
		}
		?>
	</div> <!-- end pmpro_main -->

	<div class="pmpro_sidebar">
		<div class="pmpro_section" data-visibility="shown" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
					<span class="dashicons dashicons-arrow-up-alt2"></span>
					<?php esc_html_e( 'Member Information', 'pmpro-approvals' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside">
				<div class="pmpro_member-box">
					<div class="pmpro_member-box-avatar">
						<?php echo get_avatar( (int) $user->ID, 64 ); ?>
					</div>
					<div class="pmpro_member-box-info">
						<h2><strong><?php echo esc_html( $user->display_name ); ?></strong></h2>
						<div class="pmpro_member-box-actions">
							<?php
								$member_actions = array();
								if ( function_exists( 'pmpro_member_edit_get_panels' ) ) {
									$member_actions['edit_member'] = sprintf(
										'<a href="%1$s">%2$s</a>',
										esc_url( add_query_arg( array( 'page' => 'pmpro-member', 'user_id' => (int) $user->ID ), admin_url( 'admin.php' ) ) ),
										esc_html__( 'Edit Member', 'pmpro-approvals' )
									);
								}
								$member_actions['edit_user'] = sprintf(
									'<a href="%1$s">%2$s</a>',
									esc_url( add_query_arg( array( 'user_id' => (int) $user->ID ), admin_url( 'user-edit.php' ) ) ),
									esc_html__( 'Edit User', 'pmpro-approvals' )
								);
								$member_actions_html = array();
								foreach ( $member_actions as $class => $link_html ) {
									$member_actions_html[] = sprintf( '<span class="%1$s">%2$s</span>', esc_attr( $class ), $link_html );
								}
								echo implode( ' | ', $member_actions_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					</div>
				</div>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

	</div> <!-- end pmpro_sidebar -->

</div> <!-- end pmpro_two_col -->

<?php
	require_once PMPRO_DIR . '/adminpages/admin_footer.php';
