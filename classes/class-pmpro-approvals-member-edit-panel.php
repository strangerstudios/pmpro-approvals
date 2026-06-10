<?php
/**
 * "Approvals" panel for the PMPro Member Edit screen (admin.php?page=pmpro-member).
 *
 * Shows the member's membership applications (one row per approval-required level)
 * and their full approval history log.
 *
 * @since 1.8
 */

defined( 'ABSPATH' ) || exit;

class PMPro_Approvals_Member_Edit_Panel extends PMPro_Member_Edit_Panel {

	/**
	 * @since 1.8
	 */
	public function __construct() {
		$this->slug  = 'approvals';
		$this->title = __( 'Approvals', 'pmpro-approvals' );
	}

	/**
	 * Only show the panel when the current user can edit members and at least
	 * one membership level requires approval.
	 *
	 * @since 1.8
	 */
	public function should_show() {
		if ( ! function_exists( 'pmpro_get_edit_member_capability' ) || ! current_user_can( pmpro_get_edit_member_capability() ) ) {
			return false;
		}

		$approval_levels = PMPro_Approvals::getApprovalLevels();
		return ! empty( $approval_levels );
	}

	/**
	 * @since 1.8
	 */
	protected function display_panel_contents() {
		$user = self::get_user();
		if ( empty( $user->ID ) ) {
			return;
		}

		// Collect the levels this member has an application for: either a recorded
		// approval decision or a level they currently hold that requires approval.
		$application_level_ids = array();
		foreach ( PMPro_Approvals::getApprovalLevels() as $approval_level_id ) {
			$stored    = get_user_meta( $user->ID, 'pmpro_approval_' . $approval_level_id, true );
			$has_level = PMPro_Approvals::hasMembershipLevelSansApproval( $approval_level_id, $user->ID );
			if ( ! empty( $stored ) || $has_level ) {
				$application_level_ids[] = $approval_level_id;
			}
		}

		$email_confirmation_active = function_exists( 'pmproec_load_plugin_text_domain' );
		?>
		<h3><?php esc_html_e( 'Applications', 'pmpro-approvals' ); ?></h3>
		<?php if ( empty( $application_level_ids ) ) { ?>
			<p><?php esc_html_e( 'This member has no membership applications requiring approval.', 'pmpro-approvals' ); ?></p>
		<?php } else { ?>
			<table class="widefat striped pmpro_responsive_table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Membership Level', 'pmpro-approvals' ); ?></th>
						<th><?php esc_html_e( 'Approval Status', 'pmpro-approvals' ); ?></th>
						<?php if ( $email_confirmation_active ) { ?>
							<th><?php esc_html_e( 'Email Confirmation', 'pmpro-approvals' ); ?></th>
						<?php } ?>
						<th><?php esc_html_e( 'Actions', 'pmpro-approvals' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $application_level_ids as $application_level_id ) {
						$level      = pmpro_getLevel( $application_level_id );
						/* translators: %d: membership level ID */
						$level_name = ! empty( $level->name ) ? $level->name : sprintf( __( 'Level #%d', 'pmpro-approvals' ), $application_level_id );

						if ( PMPro_Approvals::isApproved( $user->ID, $application_level_id ) ) {
							$badge_class = 'pmpro_tag-success';
							$badge_label = __( 'Approved', 'pmpro-approvals' );
						} elseif ( PMPro_Approvals::isDenied( $user->ID, $application_level_id ) ) {
							$badge_class = 'pmpro_tag-error';
							$badge_label = __( 'Denied', 'pmpro-approvals' );
						} else {
							$badge_class = 'pmpro_tag-alert';
							$badge_label = __( 'Pending', 'pmpro-approvals' );
						}

						$view_url = add_query_arg(
							array(
								'page'    => 'pmpro-approvals',
								'user_id' => (int) $user->ID,
								'l'       => (int) $application_level_id,
							),
							admin_url( 'admin.php' )
						);
						?>
						<tr>
							<td data-colname="<?php esc_attr_e( 'Membership Level', 'pmpro-approvals' ); ?>"><?php echo esc_html( $level_name ); ?></td>
							<td data-colname="<?php esc_attr_e( 'Approval Status', 'pmpro-approvals' ); ?>"><span class="pmpro_tag <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_label ); ?></span></td>
							<?php if ( $email_confirmation_active ) { ?>
								<td data-colname="<?php esc_attr_e( 'Email Confirmation', 'pmpro-approvals' ); ?>">
									<?php if ( PMPro_Approvals::getEmailConfirmation( $user->ID ) ) { ?>
										<span class="pmpro_tag pmpro_tag-success"><?php esc_html_e( 'Confirmed', 'pmpro-approvals' ); ?></span>
									<?php } else { ?>
										<span class="pmpro_tag pmpro_tag-alert"><?php esc_html_e( 'Not Confirmed', 'pmpro-approvals' ); ?></span>
									<?php } ?>
								</td>
							<?php } ?>
							<td data-colname="<?php esc_attr_e( 'Actions', 'pmpro-approvals' ); ?>"><a href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e( 'View Application', 'pmpro-approvals' ); ?></a></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		<?php } ?>

		<h3><?php esc_html_e( 'Approval History', 'pmpro-approvals' ); ?></h3>
		<?php
			// showUserLog() returns escaped markup for the per-user approval history.
			echo PMPro_Approvals::showUserLog( $user->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<?php
	}
}
