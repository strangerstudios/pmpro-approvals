<?php 

class PMPro_Approvals_Email_Template_Member_Admin_Approved extends PMPro_Email_Template {

	/**
	 * The user applying for membership.
	 *
	 * @var WP_User
	 */
	protected $member;

	/**
	 * The admin user.
	 *
	 * @var WP_User
	 */
	protected $admin;
	/**
	 * The level id
	 *
	 * @var StdClass
	 */
	protected $level;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param WP_User $member The user applying for membership.
	 * @param int $level_id The level id.
	 */
	public function __construct( WP_User $member, WP_User $admin, StdClass $level ) {
		$this->member = $member;
		$this->admin = $admin;
		$this->level_id = $level;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since TBD
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'admin_approved';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Approved (admin)', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return  esc_html__( 'This email is sent to the admin when a new member is approved.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return esc_html( sprintf( __( 'A member at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		return wp_kses_post( __( '<p>The user <a href="!!view_profile!!">!!member_name!!</a> has been approved.</p>

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
		$level = $this->$level;
		$member = $this->member;
		$admin = $this->admin;
		$view_profile = admin_url( 'admin.php?page=pmpro-approvals&user_id=' . $member->ID . '&l=' . $level->id );

		$email_template_variables = array(
			'member_name' => $member->display_name,
			'member_email' => $member->user_email,
			'membership_id' => $level->id,
			'membership_level_name' => $level->name,
			'view_profile' => $view_profile,
			'approve_link' => $view_profile . '&approve=' . $member->ID,
			'deny_link' => $view_profile . '&deny=' . $member->ID,
		);

		return $this->data = apply_filters( 'pmpro_approvals_admin_approved_email_data', $email_template_variables, $member, $admin );
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
function pmpro_approvals_email_template_member_admin_approved( $email_templates ) {
	$email_templates['admin_approved'] = 'PMPro_Approvals_Email_Template_Member_Admin_Approved';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_approvals_email_template_member_admin_approved' );