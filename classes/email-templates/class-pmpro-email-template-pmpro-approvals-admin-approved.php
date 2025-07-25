<?php 

class PMPro_Email_Template_PMProApprovals_Admin_Approved extends PMPro_Email_Template {

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
	 * The level object.
	 *
	 * @var StdClass
	 */
	protected $level;

	/**
	 * Constructor.
	 *
	 * @since 1.6.2
	 *
	 * @param WP_User $member The user applying for membership.
	 * @param int $level_id The level id.
	 */
	public function __construct( WP_User $member, WP_User $admin, StdClass $level ) {
		$this->member = $member;
		$this->admin = $admin;
		$this->level = $level;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since 1.6.2
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'admin_approved_admin';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Approved (admin)', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return  esc_html__( 'This email is sent to the admin when a new member is approved.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return esc_html( sprintf( __( 'A member at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since 1.6.2
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
	 * @since 1.6.2
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
		);
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		$level = $this->level;
		$member = $this->member;
		$admin = $this->admin;
		$view_profile = admin_url( 'admin.php?page=pmpro-approvals&user_id=' . $member->ID . '&l=' . $level->id );

		$email_template_variables = array(
			'member_name' => $member->display_name,
			'member_email' => $member->user_email,
			'membership_id' => $level->id,
			'membership_level_name' => $level->name,
			'view_profile' => $view_profile,
			'subject' => $this->get_default_subject(),
			'name' => $this->get_recipient_name(),
			'user_login' => isset( $admin->user_login ) ? $admin->user_login : "",


		);

		return apply_filters( 'pmpro_approvals_admin_approved_email_data', $email_template_variables, $member, $admin );
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since 1.6.2
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return ! empty( $this->admin->user_email ) ? $this->admin->user_email : get_bloginfo( 'admin_email' );
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since 1.6.2
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return empty( $this->admin->display_name ) ? esc_html__( 'Admin', 'pmpro-approvals' ) : $this->admin->display_name;
	}

	/**
	 * Returns the arguments to send the test email from the abstract class.
	 * Note: This requires Paid Memberships Pro V3.5 or later.
	 * 
	 * @since 1.6.5
	 * 
	 * @return array $test_data An array of contructor arguments (member, admin, level).
	 */
	public static function get_test_email_constructor_args() {
		global $current_user, $pmpro_email_test_level;

		// Get the test level.
		if ( empty( $pmpro_email_test_level ) ) {
			$levels = pmpro_getAllLevels( true );
			$pmpro_email_test_level = current( $levels );
		}
		
		// Get a random member from the users table.
		$random_user = get_users( array( 
			'number' => 1,
			'orderby' => 'ID',
			'order'  => 'DESC',
		) );

		$member = $random_user ? $random_user[0] : $current_user;	

		return array( $member, $current_user, $pmpro_email_test_level );
	}
}
/**
 * Register the email template.
 *
 * @since 1.6.2
 *
 * @param array $email_templates The email templates (template slug => email template class name)
 * @return array The modified email templates array.
 */
function pmpro_email_template_pmpro_approval_admin_approved( $email_templates ) {
	$email_templates['admin_approved_admin'] = 'PMPro_Email_Template_PMProApprovals_Admin_Approved';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_template_pmpro_approval_admin_approved' );
