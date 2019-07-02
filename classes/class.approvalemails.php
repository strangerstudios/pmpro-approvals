<?php

class PMPro_Approvals_Email extends PMProEmail {
	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PMPro_Approvals_Email();
		}

		return self::$instance;
	}

	/**
	 * Send user's an email that their account has been approved.
	 *
	 * @param $member. The member's ID or object.
	 */
	public function sendMemberApproved( $member ) {

		if ( empty( $member ) ) {
			return;
		} elseif ( is_int( $member ) ) {
			$member = get_user_by( 'ID', $member );
		}

		$level = pmpro_getMembershipLevelForUser( $member->ID );

		$this->email    = $member->user_email;
		$this->subject  = sprintf( __( 'Your membership at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
		$this->template = 'application_approved';
		$this->body     = file_get_contents( PMPRO_APP_DIR . '/email/application_approved.html' );
		$this->data     = array(
			'subject'               => $this->subject,
			'name'                  => $member->display_name,
			'member_email'          => $member->user_email,
			'user_login'            => $member->user_login,
			'sitename'              => get_option( 'blogname' ),
			'membership_id'         => $level->id,
			'membership_level_name' => $level->name,
			'siteemail'             => pmpro_getOption( 'from_email' ),
			'login_link'            => wp_login_url(),
		);
		$this->from     = pmpro_getOption( 'from' );
		$this->fromname = pmpro_getOption( 'from_name' );

		$this->data = apply_filters( 'pmpro_approvals_member_approved_email_data', $this->data, $member, $level );

		return $this->sendEmail();
	}

	/**
	 * Send user's an email that their account has been denied.
	 *
	 * @param $member. The member's ID or object.
	 */
	public function sendMemberDenied( $member ) {

		if ( empty( $member ) ) {
			return;
		} elseif ( is_int( $member ) ) {
			$member = get_user_by( 'ID', $member );
		}

		$level = pmpro_getMembershipLevelForUser( $member->ID );

		$this->email    = $member->user_email;
		$this->subject  = sprintf( __( 'Your membership at %s has been denied.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
		$this->template = 'application_denied';
		$this->body     = file_get_contents( PMPRO_APP_DIR . '/email/application_denied.html' );
		$this->data     = array(
			'subject'               => $this->subject,
			'name'                  => $member->display_name,
			'member_email'          => $member->user_email,
			'user_login'            => $member->user_login,
			'sitename'              => get_option( 'blogname' ),
			'membership_id'         => $level->id,
			'membership_level_name' => $level->name,
			'siteemail'             => pmpro_getOption( 'from_email' ),
			'login_link'            => wp_login_url(),
		);
		$this->from     = pmpro_getOption( 'from' );
		$this->fromname = pmpro_getOption( 'from_name' );

		$this->data = apply_filters( 'pmpro_approvals_member_denied_email_data', $this->data, $member );

		return $this->sendEmail();
	}


	/**
	 * Sends an email to the admin when a user has registered for a level that requires approval.
	 *
	 * @param $member The member object/ID/email.
	 * @param $admin The admin object/ID. Default $current_user object.
	 */
	public function sendAdminPending( $member = null, $admin = null ) {

		if ( empty( $admin ) ) {
			$admin = get_user_by( 'email', get_option( 'admin_email' ) );
		} elseif ( is_int( $admin ) ) {
			$admin = get_user_by( 'ID', $admin );
		}

		$this->email    = get_bloginfo( 'admin_email' );
		$this->subject  = sprintf( __( 'A member at %s is waiting approval.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
		$this->template = 'admin_notification_approval';
		$this->body     = file_get_contents( PMPRO_APP_DIR . '/email/admin_notification.html' );
		$this->data     = array(
			'subject'               => $this->subject,
			'name'                  => $admin->display_name,
			'user_login'            => $admin->user_login,
			'sitename'              => get_option( 'blogname' ),
			'siteemail'             => pmpro_getOption( 'from_email' ),
			'login_link'            => wp_login_url(),
		);
		$this->from     = pmpro_getOption( 'from' );
		$this->fromname = pmpro_getOption( 'from_name' );

		if ( ! empty( $member ) ) {

			if ( is_int( $member ) ) {
				$member = get_user_by( 'ID', $member );
			} else {
				$member = get_user_by( 'email', $member );
			}

			$level = pmpro_getMembershipLevelForUser( $member->ID );

			$this->data['member_name']  = $member->display_name;
			$this->data['member_email'] = $member->user_email;
			$this->data['membership_id']         = $level->id;
			$this->data['membership_level_name'] = $level->name;
			$this->data['view_profile'] = admin_url( 'admin.php/?page=pmpro-approvals&user_id=' . $member->ID );
			$this->data['approve_link'] = $this->data['view_profile'] . '&approve=' . $member->ID;
			$this->data['deny_link']    = $this->data['view_profile'] . '&deny=' . $member->ID;
		}

		$this->data = apply_filters( 'pmpro_approvals_admin_pending_email_data', $this->data, $member, $admin );

		return $this->sendEmail();
	}

	/**
	 * Sends an email to the admin when the user has been approved.
	 *
	 * @param $member The member object/ID/email.
	 * @param $admin The admin object/ID. Default $current_user object.
	 */
	public function sendAdminApproval( $member = null, $admin = null ) {

		if ( empty( $admin ) ) {
			$admin = get_user_by( 'email', get_option( 'admin_email' ) );
		} elseif ( is_int( $admin ) ) {
			$admin = get_user_by( 'ID', $admin );
		}

		$this->email    = get_bloginfo( 'admin_email' );
		$this->subject  = sprintf( __( 'A member at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
		$this->template = 'admin_approved';
		$this->body     = file_get_contents( PMPRO_APP_DIR . '/email/admin_approved.html' );
		$this->data     = array(
			'subject'    => $this->subject,
			'name'       => $admin->display_name,
			'user_login' => $admin->user_login,
			'sitename'   => get_option( 'blogname' ),
			'siteemail'  => pmpro_getOption( 'from_email' ),
			'login_link' => wp_login_url(),
		);
		$this->from     = pmpro_getOption( 'from' );
		$this->fromname = pmpro_getOption( 'from_name' );

		// Let's add in the user approval data if it's available.
		if ( ! empty( $member ) ) {

			if ( is_int( $member ) ) {
				$member = get_user_by( 'ID', $member );
			} else {
				$member = get_user_by( 'email', $member );
			}

			$level = pmpro_getMembershipLevelForUser( $member->ID );

			$this->data['membership_id']         = $level->id;
			$this->data['membership_level_name'] = $level->name;
			$this->data['member_email']          = $member->user_email;
			$this->data['member_name']           = $member->display_name;
			$this->data['view_profile']          = admin_url( 'admin.php/?page=pmpro-approvals&user_id=' . $member->ID );
		}

		$this->data = apply_filters( 'pmpro_approvals_admin_approved_email_data', $this->data, $member, $admin );

		return $this->sendEmail();
	}

	/**
	 * Sends an email to the admin when the user has been denied.
	 *
	 * @param $member The member object/ID/email.
	 * @param $admin The admin object/ID. Default $current_user object.
	 */
	public function sendAdminDenied( $member = null, $admin = null ) {

		if ( empty( $admin ) ) {
			$admin = get_user_by( 'email', get_option( 'admin_email' ) );
		} elseif ( is_int( $admin ) ) {
			$admin = get_user_by( 'ID', $admin );
		}

		$this->email    = get_bloginfo( 'admin_email' );
		$this->subject  = sprintf( __( 'A member at %s has been denied.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
		$this->template = 'admin_denied';
		$this->body     = file_get_contents( PMPRO_APP_DIR . '/email/admin_denied.html' );
		$this->data     = array(
			'subject'    => $this->subject,
			'name'       => $admin->display_name,
			'user_login' => $admin->user_login,
			'sitename'   => get_option( 'blogname' ),
			'siteemail'  => pmpro_getOption( 'from_email' ),
			'login_link' => wp_login_url(),
		);
		$this->from     = pmpro_getOption( 'from' );
		$this->fromname = pmpro_getOption( 'from_name' );

		// Let's add in the user approval data if it's available.
		if ( ! empty( $member ) ) {

			if ( is_int( $member ) ) {
				$member = get_user_by( 'ID', $member );
			} else {
				$member = get_user_by( 'email', $member );
			}

			$level = pmpro_getMembershipLevelForUser( $member->ID );

			$this->data['membership_id']         = $level->id;
			$this->data['membership_level_name'] = $level->name;
			$this->data['member_email']          = $member->user_email;
			$this->data['member_name']           = $member->display_name;
			$this->data['view_profile']          = admin_url( 'admin.php/?page=pmpro-approvals&user_id=' . $member->ID );
		}

		$this->data = apply_filters( 'pmpro_approvals_admin_denied_email_data', $this->data, $member, $admin );

		return $this->sendEmail();
	}
}
PMPro_Approvals_Email::get_instance();
