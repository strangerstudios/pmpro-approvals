<?php 

class PMPro_Approvals_Email_Template_Member_Admin_Denied extends PMPro_Email_Template {

	/**
	 * The user applying for membership.
	 *
	 * @var WP_User
	 */
	protected $member;

	/**
	 * The level id
	 *
	 * @var int
	 */
	protected $level_id;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param WP_User $member The user applying for membership.
	 * @param int $level_id The level id.
	 */
	public function __construct( WP_User $member, int $level_id ) {
		$this->member = $member;
		$this->level_id = $level_id;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since TBD
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'admin_denied';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Denied (admin)', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return esc_html__( 'This email is sent to the admin when a member is denied.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return esc_html( sprintf( __( 'A member at %s has been denied.', 'pmpro-approvals' ), get_bloginfo( 'name' ) ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		return wp_kses_post( __( '<p>The user <a href="!!view_profile!!">!!member_name!!</a> has been denied.</p>

<p>Log in to your membership account here: !!login_link!!</p>' ), 'pmpro-approvals' );
	}


	/**
	 * Get the email template variables for the email paired with a description of the variable.
	 *
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		return array(
			'!!member_name!!' => esc_html__( 'The name of the member.', 'pmpro-approvals' ),
			'!!member_email!!' => esc_html__( 'The email address of the member.', 'pmpro-approvals' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'pmpro-approvals' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'pmpro-approvals' ),
			'!!view_profile!!' => esc_html__( 'The URL of the profile page for the member.', 'pmpro-approvals' ),
			'!!approve_link!!' => esc_html__( 'The URL to approve the member.', 'pmpro-approvals' ),
			'!!deny_link!!' => esc_html__( 'The URL to deny the member.', 'pmpro-approvals' ),
		);
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		$level = pmpro_getSpecificMembershipLevelForUser( $this->$member->ID, $this->$level_id );
		$view_profile = admin_url( 'admin.php?page=pmpro-approvals&user_id=' . $this->member->ID . '&l=' . $this->level_id );

		$email_template_variables = array(
			'member_name' => $this->member->display_name,
			'member_email' => $this->member->user_email,
			'membership_id' => $this->level_id,
			'membership_level_name' => $level->name,
			'view_profile' => $view_profile,
			'approve_link' => $view_profile . '&approve=' . $this->member->ID,
			'deny_link' => $view_profile . '&deny=' . $this->member->ID,
		);

		return $email_template_variables;
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since TBD
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since TBD
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		//get user by email
		$user = get_user_by( 'email', $this->get_recipient_email() );
		return empty( $user->display_name ) ? esc_html__( 'Admin', 'paid-memberships-pro' ) : $user->display_name;
	}
}
/**
 * Register the email template.
 *
 * @since TBD
 *
 * @param array $email_templates The email templates (template slug => email template class name)
 * @return array The modified email templates array.
 */
function pmpro_approval_email_template_member_admin_denied( $email_templates ) {
	$email_templates['admin_denied'] = 'PMPro_Approvals_Email_Template_Member_Admin_Denied';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_approval_email_template_member_admin_denied' );