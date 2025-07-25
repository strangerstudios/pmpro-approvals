<?php 

class PMPro_Email_Template_PMProApprovals_Application_Approved extends PMPro_Email_Template {

	/**
	 * The user applying for membership.
	 *
	 * @var WP_User
	 */
	protected $member;

	/**
	 * The level id
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
	public function __construct( WP_User $member, StdClass $level ) {
		$this->member = $member;
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
		return 'application_approved';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Approved', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return esc_html__( 'This Email is sent to the member when their membership application is approved.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return sprintf( __( 'Your membership at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		return wp_kses_post( __( '<p>Your membership account at !!sitename!! has been approved.</p>
<p>Log in to your membership account here: !!login_link!!</p>' ) );
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
			'!!member_email!!' => esc_html__( 'The email address of the member.', 'pmpro-approvals' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'pmpro-approvals' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'pmpro-approvals' ),
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

		$email_template_variables = array(
			'subject' => $this->get_default_subject(),
			'name' => $member->display_name,
			'member_email' => $member->user_email,
			'user_login' => $member->user_login,
			'membership_id' => $level->id,
			'membership_level_name' => $level->name,
		);
		return apply_filters( 'pmpro_approvals_member_approved_email_data', $email_template_variables, $member, $level );

	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since 1.6.2
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return $this->member->user_email;
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since 1.6.2
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return $this->member->display_name;
	}

	/**
	 * Returns the arguments to send the test email from the abstract class.
	 * Note: This requires Paid Memberships Pro V3.5 or later.
	 * 
	 * @since TBD
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

		return array( $member, $pmpro_email_test_level );
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
function pmpro_email_template_pmpro_approvals_application_approved( $email_templates ) {
	$email_templates['application_approved'] = 'PMPro_Email_Template_PMProApprovals_Application_Approved';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_template_pmpro_approvals_application_approved' );