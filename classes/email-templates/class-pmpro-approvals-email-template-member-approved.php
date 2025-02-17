<?php 

class PMPro_Approvals_Email_Template_Member_Approved extends PMPro_Email_Template {

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
		return 'application_approved';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Approved', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since TBD
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return esc_html__( 'This Email is sent to the member when their membership application is approved.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since TBD
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return sprintf( __( 'Your membership at %s has been approved.', 'pmpro-approvals' ), get_bloginfo( 'name' ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		return array(
			'!!subject!!' => esc_html__( 'The subject of the email.', 'pmpro-approvals' ),
			'!!name!!' => esc_html__( 'The name of the member.', 'pmpro-approvals' ),
			'!!member_email!!' => esc_html__( 'The email address of the member.', 'pmpro-approvals' ),
			'!!user_login!!' => esc_html__( 'The username of the member.', 'pmpro-approvals' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'pmpro-approvals' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'pmpro-approvals' ),
			'!!login_link!!' => esc_html__( 'The URL of the login page.', 'pmpro-approvals' ),
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

		$email_template_variables = array(
			'!!subject!!' => $this->get_default_subject(),
			'!!name!!' => $this->member->display_name,
			'!!member_email!!' => $this->member->user_email,
			'!!user_login!!' => $this->member->user_login,
			'!!membership_id!!' => $this->level_id,
			'!!membership_level_name!!' => $level->name,
			'!!login_link!!' => pmpro_login_url(),
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
		return $this->member->user_email;
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since TBD
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return $this->member->display_name;
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
function pmpro_approved_email_template_member_approved( $email_templates ) {
	$email_templates['application_approved'] = 'PMPro_Approvals_Email_Template_Member_Approved';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_approved_email_template_member_approved' );