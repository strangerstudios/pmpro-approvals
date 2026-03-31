<?php 

class PMPro_Email_Template_PMProApprovals_Admin_Notification_Approval extends PMPro_Email_Template {

	/**
	 * The user applying for membership.
	 *
	 * @var WP_User
	 */
	protected $member;

	/**
	 * The level id
	 *
	 * @var stdClass
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
	public function __construct( WP_User $member, stdClass $level ) {
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
		return 'admin_notification_approval_admin';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Approval Pending (admin)', 'pmpro-approvals' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since 1.6.2
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return esc_html__( 'This email is sent to the admin when a new member is waiting for approval.', 'pmpro-approvals' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return esc_html( sprintf( __( 'A member at %s is waiting approval.', 'pmpro-approvals' ), get_bloginfo( 'name' ) ) );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since 1.6.2
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		if ( ! class_exists( 'PMPro_Liquid_Renderer' ) ) {
			// Running a version of PMPro before liquid email rendering was available.
			return wp_kses_post( __( '<p>The user !!member_name!! is pending approval.</p>

<p>View the user\'s profile here: !!view_profile!!</p>', 'pmpro-approvals' ) );
		}
		return wp_kses_post( __( '<p>The user {{ member_name }} is pending approval.</p>

<p>View the user\'s profile here: {{ view_profile }}</p>', 'pmpro-approvals' ) );
	}


	/**
	 * Get the email template variables for the email paired with a description of the variable.
	 *
	 * @since 1.6.2
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		if ( ! class_exists( 'PMPro_Liquid_Renderer' ) ) {
			// Running a version of PMPro before liquid email rendering was available.
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
		return array(
			'{{ member_name }}' => esc_html__( 'The name of the member.', 'pmpro-approvals' ),
			'{{ member_email }}' => esc_html__( 'The email address of the member.', 'pmpro-approvals' ),
			'{{ membership_id }}' => esc_html__( 'The ID of the membership level.', 'pmpro-approvals' ),
			'{{ membership_level_name }}' => esc_html__( 'The name of the membership level.', 'pmpro-approvals' ),
			'{{ view_profile }}' => esc_html__( 'The URL of the profile page for the member.', 'pmpro-approvals' ),
			'{{ approve_link }}' => esc_html__( 'The URL to approve the member.', 'pmpro-approvals' ),
			'{{ deny_link }}' => esc_html__( 'The URL to deny the member.', 'pmpro-approvals' ),
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
		$view_profile = admin_url( 'admin.php?page=pmpro-approvals&user_id=' . $member->ID . '&l=' . $level->id );
		$email_template_variables = array(
			'member_name' => $member->display_name,
			'member_email' => $member->user_email,
			'membership_id' => $level->id,
			'membership_level_name' => $level->name,
			'view_profile' => $view_profile,
			'approve_link' => $view_profile . '&approve=' . $member->ID,
			'deny_link' => $view_profile . '&deny=' . $member->ID,
			'subject' => $this->get_default_subject(),
			'name' => $this->get_recipient_name(),
			'user_login' => $this->get_recipient_name()
		);

		// Preserve backward compatibility by passing an admin context object  
        // (WP_User when available, or false otherwise) as the third argument.  
        $admin_user  = false;  
        $admin_email = $this->get_recipient_email();  
        if ( ! empty( $admin_email ) && function_exists( 'is_email' ) && is_email( $admin_email ) ) {  
            $resolved_admin = get_user_by( 'email', $admin_email );  
            if ( $resolved_admin instanceof WP_User ) {  
                $admin_user = $resolved_admin;  
            }
		}

		return apply_filters( 'pmpro_approvals_admin_pending_email_data', $email_template_variables, $member, $admin_user );
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since 1.6.2
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since 1.6.2
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		$user = get_user_by( 'email', $this->get_recipient_email() );
		return empty( $user->display_name ) ? esc_html__( 'Admin', 'pmpro-approvals' ) : $user->display_name;
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
function pmpro_email_template_pmpro_approvals_admin_notification_approval( $email_templates ) {
	$email_templates['admin_notification_approval_admin'] = 'PMPro_Email_Template_PMProApprovals_Admin_Notification_Approval';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_template_pmpro_approvals_admin_notification_approval' );
