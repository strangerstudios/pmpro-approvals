<?php
/*
Plugin Name: Paid Memberships Pro - Approvals Add On
Plugin URI: http://www.paidmembershipspro.com/
Description: Grants administrators the ability to approve/deny memberships after signup.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
Text Domain: pmpro-approvals
*/

class PMPro_Approvals {
    /*
		Attributes
	*/
    private static $instance = null;		// Refers to a single instance of this class.

    /**
	 * Constructor
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    private function __construct() {		
		//initialize the plugin
  		add_action( 'init', array( 'PMPro_Approvals', 'init' ) );

    }

    /**
     * Creates or returns an instance of this class.
     *
     * @return  PMPro_Approvals A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

	/**
	 * Run code on init.
	 */
    public static function init(){
		//check that PMPro is active
		if(!defined('PMPRO_VERSION'))
			return;
		
		//add admin menu items to 'Memberships' in WP dashboard and admin bar
  		add_action( 'admin_menu', array( 'PMPro_Approvals', 'admin_menu' ) );
  		add_action( 'admin_bar_menu', array( 'PMPro_Approvals', 'admin_bar_menu' ), 1000 );
      	add_action( 'admin_init', array( 'PMPro_Approvals', 'admin_init' ) );

		//add user actions to the approvals page
		add_filter( 'pmpro_approvals_user_row_actions', array( 'PMPro_Approvals', 'pmpro_approvals_user_row_actions' ), 10, 2 );
		
		//add approval section to edit user page
		$membership_level_capability = apply_filters("pmpro_edit_member_capability", "manage_options");
		if(current_user_can($membership_level_capability))
			//current user can change membership levels
			add_action('pmpro_after_membership_level_profile_fields', array( 'PMPro_Approvals', 'show_user_profile_status' ), 5 );
		else {
			//current user can't change membership level; use different hooks
			add_action('edit_user_profile', array( 'PMPro_Approvals', 'show_user_profile_status' ) );
			add_action('show_user_profile', array( 'PMPro_Approvals', 'show_user_profile_status' ) );
		}
		
		//check approval status at checkout
		add_action('pmpro_checkout_preheader', array( 'PMPro_Approvals', 'pmpro_checkout_preheader_block_denied_members' ));
				
		//filter membership and content access
		add_filter( 'pmpro_has_membership_level', array( 'PMPro_Approvals', 'pmpro_has_membership_level' ), 10, 3 );
		add_filter( 'pmpro_has_membership_access_filter', array( 'PMPro_Approvals', 'pmpro_has_membership_access_filter' ), 10, 4 );
		
  		//add settings to the edit membership level page
		/*
  			Add settings to edit level page: (see pmpro-shipping)
  			* add_action pmpro_membership_level_after_other_settings
  			* add_action pmpro_save_membership_level
  		*/		
	    //load checkbox in membership level edit page for users to select.
	    add_action( 'pmpro_membership_level_after_other_settings', array( 'PMPro_Approvals', 'pmpro_membership_level_after_other_settings' ) );
		add_action( 'pmpro_save_membership_level', array( 'PMPro_Approvals', 'pmpro_save_membership_level' ) );				
		
		//Add code for filtering checkouts, confirmation, and content filters
		add_filter( 'pmpro_non_member_text_filter', array( 'PMPro_Approvals', 'pmpro_non_member_text_filter' ) );
		add_action( 'pmpro_account_bullets_top', array( 'PMPro_Approvals', 'pmpro_account_bullets_top' ) );
		add_filter( 'pmpro_confirmation_message', array( 'PMPro_Approvals', 'pmpro_confirmation_message' ) );
		add_action( 'pmpro_before_change_membership_level', array( 'PMPro_Approvals', 'pmpro_before_change_membership_level' ), 10, 2 );
		add_action( 'pmpro_after_change_membership_level', array( 'PMPro_Approvals', 'pmpro_after_change_membership_level' ), 10, 2 );		

		//add support for PMPro Email Templates Add-on
		add_filter( 'pmproet_templates', array( 'PMPro_Approvals', 'pmproet_templates' ) );
    }

    /**
    * Run code on admin init
    */
    public static function admin_init(){
    	//TODO: Add Approver role (maybe in activation/deactivation)
		
        //get role of administrator
        $role = get_role( 'administrator' );
        //add custom capability to administrator
        $role->add_cap( 'pmpro_approvals' );
		
		//make sure the current user has the updated cap
		global $current_user;
		setup_userdata( $current_user->ID );
    }

	/**
	 * Create the submenu item 'Approvals' under the 'Memberships' link in WP dashboard.
	 * Fires during the "admin_menu" action.
	 */
    public static function admin_menu(){
		add_submenu_page( 'pmpro-membershiplevels', __( 'Approvals', 'pmpro-approvals' ), __( 'Approvals', 'pmpro-approvals' ), 'pmpro_approvals', 'pmpro-approvals', array( 'PMPro_Approvals', 'admin_page_approvals' ) );
    }
	
	/**
	 * Create 'Approvals' link under the admin bar link 'Memberships'.
	 * Fires during the "admin_bar_menu" action.
	 */
    public static function admin_bar_menu(){
  		global $wp_admin_bar;

  		//check capabilities (TODO: Define a new capability (pmpro_approvals) for managing approvals.)
  		if ( ! is_super_admin() && ! current_user_can( 'pmpro_approvals' ) || ! is_admin_bar_showing() ){
  			return;
      	}

  		//add the admin link
  		$wp_admin_bar->add_menu( array(
  			'id'    => 'pmpro-approvals',
  			'title' => __( 'Approvals', 'pmpro-approvals' ),
  			'href'  => get_admin_url( NULL, '/admin.php?page=pmpro-approvals' ),
  			'parent'=>'paid-memberships-pro'
  		));
	}

	/**
	 * Load the Approvals admin page.
	 */
	public static function admin_page_approvals() {
	if(!empty($_REQUEST['user_id']))
		require_once( dirname( __FILE__ ) . '/adminpages/userinfo.php' );
	else
		require_once( dirname( __FILE__ ) . '/adminpages/approvals.php' );
	}

	/**
	 * Get options for level.
	 */
	public static function getOptions( $level_id = NULL ) {
		$options = get_option( 'pmproapp_options', array() );
		
		if( !empty( $level_id ) ) {
			if( !empty( $options[$level_id] ) )
				return $options[$level_id];
			else
				return array( 'requires_approval' => false );
		} else {
			return $options;
		}
	}
	
	/**
	 * Save options for level.
	 */
	public static function saveOptions( $options ) {
		update_option( 'pmproapp_options', $options, 'no' );
	}
	
	/**
	 * Check if a level requires approval
	 */
	public static function requiresApproval( $level_id = NULL ) {
		//no level?
		if(empty($level_id))
			return false;
		
		$options = PMPro_Approvals::getOptions($level_id);
		return $options['requires_approval'];
	}	

	/**
	* Load check box to make level require membership.
	* Fires on pmpro_membership_level_after_other_settings
	*/
	public static function pmpro_membership_level_after_other_settings(){
		$level_id = $_REQUEST['edit'];
				
		if($level_id > 0)
			$options = PMPro_Approvals::getOptions( $level_id );
		else
			$options = array( 'requires_approval' => false );
			
		?>
		<h3 class="topborder"><?php _e( 'Approval Settings', 'pmpro-approvals' ) ?></h3>
		<table>
		<tbody class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="requires_approval"><?php _e( 'Requires Approval:', 'pmpro-approvals' );?></label></th>
				<td>
					<input type="checkbox" id="requires_approval" name="requires_approval" value="1" <?php checked( $options['requires_approval'], 1);?> />
					<label for="requires_approval"><?php _e( 'Check this if membership requires approval before users are assigned to this membership level.', 'pmpro-approvals' );?></label>
				</td>
			</tr>
		</tbody>
		</table>
		<?php
	}

	/**
	 * Save settings when editing the membership level
	 * Fires on pmpro_save_membership_level
	 */
	public static function pmpro_save_membership_level( $level_id ) {
		//get value
		if( !empty( $_REQUEST['requires_approval'] ) )
			$requires_approval = true;
		else
			$requires_approval = false;
		
		//get options
		$options = PMPro_Approvals::getOptions();
		
		//create array if we don't have options for this level already
		if( empty( $options[$level_id] ) )
			$options[$level_id] = array();
			
		//update requires_approval option
		$options[$level_id]['requires_approval'] = $requires_approval;
		
		//save it
		PMPro_Approvals::saveOptions( $options );
	}
	
	/**
	 * Deny access to member content if user is not approved
	 * Fires on pmpro_has_membership_access_filter
	 */
	public static function pmpro_has_membership_access_filter( $access, $post, $user, $levels ) {
		
		//if we don't have access now, we still won't
		if(!$access)
			return $access;
		
		//no user, this must be open to everyone
		if(empty($user) || empty($user->ID))
			return $access;
		
		//no levels, must be open
		if(empty($levels))
			return $access;
		
		//now we need to check if the user is approved for ANY of the $levels
		$access = false;	//assume no access
		foreach($levels as $level) {			
			if(PMPro_Approvals::isApproved($user->ID, $level->id)) {
				$access = true;
				break;
			}else{
				$access = false;
				break;
		}
		
		return $access;
	}
	
	/**
	 * Filter hasMembershipLevel to return false
	 * if a user is not approved.
	 * Fires on pmpro_has_membership_level filter
	 */
	public static function pmpro_has_membership_level( $haslevel, $user_id, $levels ) {
		
		//if already false, skip
		if(!$haslevel)
			return $haslevel;
		
		//no user, skip
		if(empty($user_id))
			return $haslevel;
		
		//no levels, skip
		if(empty($levels))
			return $haslevel;
		
		//now we need to check if the user is approved for ANY of the $levels
		$haslevel = false;
		foreach($levels as $level) {
			if(PMPro_Approvals::isApproved($user_id, $level)) {
				$haslevel = true;
				break;
			}
		}
		
		return $haslevel;
	}
	
	/**
	 * Show an error if a denied member is attempting to checkout for a level they are already denied for.
	 * Note that the precense of this error will halt checkout as well.
	 */
	public static function pmpro_checkout_preheader_block_denied_members() {
		global $pmpro_level;
		
		if(PMPro_Approvals::isDenied(NULL, $pmpro_level->id)) {
			pmpro_setMessage(__('Your previous application for this level has been denied. You will not be allowed to check out.', 'pmpro-approvals'), 'pmpro_error');
		}
	}		
	
	/**
	 * Get User Approval Meta
	 */
	public static function getUserApproval($user_id = NULL, $level_id = NULL) {
		//default to false
		$user_approval = false;		//false will function as a kind of N/A at times
		
		//default to the current user
		if(empty($user_id)) {
			global $current_user;
			$user_id = $current_user->ID;
		}
				
		//get approval status for this level from user meta
		if(!empty($user_id)) {
			//default to the user's current level
			if(empty($level_id)) {
				$level = pmpro_getMembershipLevelForUser($user_id);				
				if(!empty($level))
					$level_id = $level->id;
			}
				
			//if we have a level, check if it requires approval and if so check user meta
			if(!empty($level_id)) {
				//if the level doesn't require approval, then the user is approved
				if(!PMPro_Approvals::requiresApproval($level_id)) {
					//approval not required, so return status approved
					$user_approval = array('status'=>'approved');
				} else {
					//approval required, check user meta
					$user_approval = get_user_meta( $user_id, 'pmpro_approval_' . $level_id, true);					
				}
			}
		}
		
		return $user_approval;
	}
	
	/**
	 * Returns status of a given or current user. Returns 'approved', 'denied' or 'pending'.
	 * If the users level does not require approval it will not return anything.
	 */
	public static function getUserApprovalStatus( $user_id = NULL, $level_id = NULL, $short = true){

		global $current_user;

		//check if user ID is blank, set to current user ID.
		if( empty( $user_id ) ){
			$user_id = $current_user->ID;
		}

		//get the PMPro level for the user
		if(empty($level_id)) {
			$level = pmpro_getMembershipLevelForUser($user_id);
			$level_id = $level->ID;
		} else {
			$level = pmpro_getLevel($level_id);
		}

		//make sure we have a user and level by this point
		if(empty($user_id) || empty($level_id))
			return false;
		
		//check if level requires approval.
		if( !PMPro_Approvals::requiresApproval( $level_id ) ){
			return;
		}

		//Get the user approval status. If it's not Approved/Denied it's set to Pending.		
		if( !PMPro_Approvals::isPending( $user_id, $level_id ) ){

			$approval_data = PMPro_Approvals::getUserApproval( $user_id, $level_id );						
						
			if($short) {
				if(!empty($approval_data))
					$status = $approval_data['status'];
				else
					$status = 'approved';
			} else {
				if(!empty($approval_data)) {
					$approver = get_userdata($approval_data['who']);
					if(current_user_can('edit_users'))
						$approver_text = '<a href="'. get_edit_user_link( $approver->ID ) .'">'. esc_attr( $approver->display_name ) .'</a>';
					elseif(current_user_can('pmpro_approvals'))
						$approver_text = $approver->display_name;
					else
						$approver_text = '';

					if($approver_text)
						$status = sprintf(__('%s on %s by %s', 'pmpro-approvals'), ucwords($approval_data['status']), date_i18n(get_option('date_format'), $approval_data['timestamp']), $approver_text);
					else
						$status = sprintf(__('%s on %s', 'pmpro-approvals'), ucwords($approval_data['status']), date_i18n(get_option('date_format'), $approval_data['timestamp']));
				} else {
					$status = __('Approved', 'pmpro-approvals');
				}
			}

		}else{

			if($short)
				$status = __( 'pending', 'pmpro-approvals' );
			else
				$status = sprintf(__('Pending Approval for %s', 'pmpro-approvals'), $level->name);

		}

		return $status;
	}
	
	/**
	 * Get user approval statuses for all levels that require approval
	 * Level IDs are used for the index the array
	 */
	public static function getUserApprovalStatuses( $user_id = NULL, $short = false ) {
		//default to current user
		if(empty($user_id)) {
			global $current_user;
			$user_id = $current_user->ID;
		}
		
		$approval_levels = PMPro_Approvals::getApprovalLevels();
		$r = array();
		foreach($approval_levels as $level_id) {
			$r[$level_id] = PMPro_Approvals::getUserApprovalStatus( $user_id, $level_id, $short );			
		}
		
		return $r;
	}
		
	/**
	 * Check if a user is approved.
	 */
	public static function isApproved($user_id = NULL, $level_id = NULL) {	
		//default to the current user
		if(empty($user_id)) {
			global $current_user;
			$user_id = $current_user->ID;
		}		
		
		//get approval for this user/level
		$user_approval = PMPro_Approvals::getUserApproval($user_id, $level_id);
				
		//if no array, check if they already have the level
		if(empty($user_approval) || !is_array($user_approval)) {			
			$level = pmpro_getMembershipLevelForUser($user_id);
			
			if(empty($level) || (!empty($level_id) && $level->id != $level_id))
				return false;
			else
				return true;
		}
		
		//otherwise, let's check the status		
		if($user_approval['status'] == 'approved')
			return true;
		else
			return false;
	}
	
	/**
	 * Check if a user is approved.
	 */
	public static function isDenied($user_id = NULL, $level_id = NULL) {	
		//get approval for this user/level
		$user_approval = PMPro_Approvals::getUserApproval($user_id, $level_id);
		
		//if no array, return false
		if(empty($user_approval) || !is_array($user_approval))
			return false;
		
		//otherwise, let's check the status		
		if($user_approval['status'] == 'denied')
			return true;
		else
			return false;
	}
	
	/**
	 * Check if a user is pending
	 */
	public static function isPending($user_id = NULL, $level_id = NULL) {
		//default to the current user
		if(empty($user_id)) {
			global $current_user;
			$user_id = $current_user->ID;
		}						
		
		//get approval for this user/level
		$user_approval = PMPro_Approvals::getUserApproval($user_id, $level_id);
				
		//if no array, check if they already had the level
		if(empty($user_approval) || !is_array($user_approval)) {
			$level = pmpro_getMembershipLevelForUser($user_id);

			if(empty($level) || (!empty($level_id) && $level->id != $level_id))
				return true;
			else
				return false;
		}
				
		//otherwise, let's check the status		
		if($user_approval['status'] == 'pending')
			return true;
		else
			return false;
	}
	
	/**
	 * Get levels that require approval
	 */
	public static function getApprovalLevels() {
		$options = PMPro_Approvals::getOptions();

		$r = array();

		foreach ($options as $level_id => $level_options) {
			if($level_options['requires_approval']){
				$r[] = $level_id;
			}
		}
		return $r;
	}
	
	/**
	 * Get list of approvals
	 */
	public static function getApprovals( $l = false, $s = '', $status = 'pending', $sortby = 'user_registered', $sortorder = 'ASC', $pn = 1, $limit = 15 ) {		
		global $wpdb;
		
		$end = $pn * $limit;
		$start = $end - $limit;				
				
		$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id ";
		
		if(!empty($status) && $status != 'all')
			$sqlQuery .= "LEFT JOIN $wpdb->usermeta um ON um.user_id = u.ID AND um.meta_key LIKE CONCAT('pmpro_approval_', mu.membership_id) ";
		
		$sqlQuery .= "WHERE mu.status = 'active' AND mu.membership_id > 0 ";
		
		if(!empty($s))
			$sqlQuery .= "AND (u.user_login LIKE '%" . esc_sql($s) . "%' OR u.user_email LIKE '%" . esc_sql($s) . "%' OR u.display_name LIKE '%" . esc_sql($s) . "%') ";
		
		if($l)
			$sqlQuery .= " AND mu.membership_id = '" . esc_sql($l) . "' ";
		else
			$sqlQuery .= " AND mu.membership_id IN(" . implode(',', PMPro_Approvals::getApprovalLevels()) . ") ";
		
		if(!empty($status) && $status != 'all')
			$sqlQuery .= "AND um.meta_value LIKE '%\"" . esc_sql($status) . "\"%' ";
		
		//$sqlQuery .= "GROUP BY u.ID ";
		
		if($sortby == "pmpro_approval")
			$sqlQuery .= "ORDER BY (um2.meta_value IS NULL) $sortorder ";		
		else
			$sqlQuery .= "ORDER BY $sortby $sortorder ";
			
		$sqlQuery .= "LIMIT $start, $limit";	
						
		$theusers = $wpdb->get_results($sqlQuery);		
		
		return $theusers;		
	}
	
	/**
	 * Approve a member
	 */
	public static function approveMember( $user_id, $level_id = NULL ) {
		global $current_user, $msg, $msgt;
		
		//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmpro-approvals');
			
			return false;
		}
		
		//get user's current level if none given
		if(empty($level_id)) {
			$user_level = pmpro_getMembershipLevelForUser($user_id);
			$level_id = $user_level->id;
		}
		
		//update user meta to save timestamp and user who approved
		update_user_meta($user_id, 'pmpro_approval_' . $level_id, array('status'=>'approved', 'timestamp'=>current_time('timestamp'), 'who' => $current_user->ID, 'approver'=>$current_user->user_login));
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was approved.", 'pmpro-approvals');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membership at %s has been approved.", 'pmpro-approvals'), get_bloginfo('name'));
		$approval_email->template = "application_approved";
		$approval_email->body .= file_get_contents( dirname( __FILE__ ) . "/email/application_approved.html" );
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url());
		$approval_email->sendEmail();
		
		//Send approval email to admin too
		$admin_approval_email = new PMProEmail();
		$admin_approval_email->email = get_bloginfo( 'admin_email' );
		$admin_approval_email->subject = sprintf(__("A membership at %s has been approved.", 'pmpro-approvals'), get_bloginfo('name'));
		$admin_approval_email->template = "admin_approved";
		$admin_approval_email->body .= file_get_contents( dirname( __FILE__ ) . "/email/admin_approved.html" );
		$admin_approval_email->sendEmail();
		
		return true;
	}

	/**
	 * Deny a member
	 */
	public static function denyMember( $user_id, $level_id ) {
		global $current_user, $msg, $msgt;

		//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmpro-approvals');
			
			return false;
		}
		
		//get user's current level if none given
		if(empty($level_id)) {
			$user_level = pmpro_getMembershipLevelForUser($user_id);
			$level_id = $user_level->id;
		}
		
		//update user meta to save timestamp and user who approved
		update_user_meta( $user_id, 'pmpro_approval_' . $level_id, array( "status"=>"denied", "timestamp"=>time(), "who" => $current_user->ID, "approver"=>$current_user->user_login ) );
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was denied.", 'pmpro-approvals');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membeship at %s has been denied.", 'pmpro-approvals'), get_bloginfo('name'));
		$approval_email->template = "application_denied";
		$approval_email->body .= file_get_contents( dirname(__FILE__) . "/email/application_denied.html" );
		
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url()); //Update this?
		$approval_email->sendEmail();

		//Send denied email to admin too
		$admin_approval_email = new PMProEmail();
		$admin_approval_email->email = get_bloginfo( 'admin_email' );
		$admin_approval_email->subject = sprintf(__("A membership at %s has been denied.", 'pmpro-approvals'), get_bloginfo('name'));
		$admin_approval_email->template = "admin_denied";
		$admin_approval_email->body .= file_get_contents( dirname( __FILE__ ) . "/email/admin_denied.html" );
		$admin_approval_email->sendEmail();
		
		return true;
 
	}

	/**
	 * Reset a member to pending approval status
	 */
	public static function resetMember( $user_id, $level_id ) {
		global $current_user, $msg, $msgt;
		
    	//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmpro-approvals');
			
			return false;
		}
		
		//get user's current level if none given
		if(empty($level_id)) {
			$user_level = pmpro_getMembershipLevelForUser($user_id);
			$level_id = $user_level->id;
		}
		
		update_user_meta($user_id, "pmpro_approval_" . $level_id, array('status'=>'pending', 'timestamp'=>current_time('timestamp'), 'who' => '', 'approver'=>''));
			
		$msg = 1;
		$msgt = __("Approval reset.", 'pmpro-approvals');	
		
		return true;

	}

	/**
	 * Set approval status to pending for new members
	 */
	public static function pmpro_before_change_membership_level( $level_id, $user_id ) {
				
		//check if level requires approval, if not stop executing this function and don't send email.
		if( !PMPro_Approvals::requiresApproval( $level_id ) ){
			return;
		}	
				
		//if they are already approved, keep them approved
		if(PMPro_Approvals::isApproved($user_id, $level_id))
			return;
		
		//if they are denied, keep them denied (we're blocking checkouts elsewhere, so this is an admin change/etc)
		if(PMPro_Approvals::isDenied($user_id, $level_id))
			return;
		
		//if this is their current level, assume they were grandfathered in and leave it alone		
		if(pmpro_hasMembershipLevel($level_id, $user_id))
			return;
		
		//else, we need to set their status to pending
		update_user_meta($user_id, "pmpro_approval_" . $level_id, array('status'=>'pending', 'timestamp'=>current_time('timestamp'), 'who' => '', 'approver'=>''));
	}
	
	/**
	 * Send an email to an admin when a user has signed up for a membership level that requires approval.	 
	 */
	public static function pmpro_after_change_membership_level( $level_id, $user_id ){

		//check if level requires approval, if not stop executing this function and don't send email.
		if( !PMPro_Approvals::requiresApproval( $level_id ) ){
			return;
		}				
		
		//get admin email address to email admin.
		$admin_email = get_bloginfo( 'admin_email' );

		$admin_approval_email = new PMProEmail();

		$admin_approval_email->email = $admin_email;
		$admin_approval_email->subject = __( 'A user is pending approval for a level', 'pmpro-approvals' );
		$admin_approval_email->template = 'admin_notification_approved'; //Update email template for admins.
		$admin_approval_email->body .= __( '<p>Dear Admin</p>', 'pmpro-approvals' );
		$admin_approval_email->body .= file_get_contents( dirname( __FILE__ ) . "/email/admin_notification.html" );
		$admin_approval_email->body .= '<p><a href=' .get_admin_url(). 'admin.php?page=pmpro-approvals&user_id=' . $user_id . '>Preview user details</a><p>';
		
		$admin_approval_email->sendEmail();

	}

	/**
	 * Show a different message for users that have their membership awaiting approval.
	 */
	public static function pmpro_non_member_text_filter( $text ){

		global $current_user, $has_access;

		//if a user does not have a membership level, return default text.
		if( !pmpro_hasMembershipLevel() ){
			return $text;
		}else{
			//get current user's level ID
			$users_level = pmpro_getMembershipLevelForUser($current_user->ID);
			$level_id = $users_level->ID;
				if( PMPro_Approvals::requiresApproval( $level_id ) && PMPro_Approvals::isPending() ){
					$text = __( 'Your membership requires approval before you are able to view this content.', 'pmpro-approvals' );
				}elseif( PMPro_Approvals::requiresApproval( $level_id ) && PMPro_Approvals::isDenied() ) {
			$text = __( 'Your membership application has been denied. Contact the site owners if you believe this is an error.', 'pmpro-approvals' );
			}
		}

		return $text;
	}
	
	/**
	 * Set user action links for approvals page
	 */
	public static function pmpro_approvals_user_row_actions($actions, $user) {
		$cap = apply_filters('pmpro_approvals_cap', 'pmpro_approvals');		
	
		if(current_user_can('edit_users') && !empty($user->ID))
			$actions[] = '<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '">Edit</a>';
	
		if(current_user_can($cap) && !empty($user->ID))
			$actions[] = '<a href="' . admin_url('admin.php?page=pmpro-approvals&user_id=' . $user->ID) . '">Preview</a>';		
		
		return $actions;
	}

	/**
	 * Add Approvals status to Account Page.
	 */
	public static function pmpro_account_bullets_top(){

			$approval_status = PMPro_Approvals::getUserApprovalStatus();

			printf( __( '<li><strong>Status:</strong> %s</li>', 'pmpro-approvals'), $approval_status );

	}

	/**
	 * Custom confirmation message for levels that requires approval.
	 */

	public static function pmpro_confirmation_message( $confirmation_message ){

		global $current_user;

		$approval_status = PMPro_Approvals::getUserApprovalStatus();

		$users_level = pmpro_getMembershipLevelForUser($current_user->ID);
		$level_id = $users_level->ID;

		//if current level does not require approval keep confirmation message the same.
		if( !PMPro_Approvals::requiresApproval( $level_id ) ){
			return $confirmation_message;
		}

		$confirmation_message = "<p>" . sprintf(__('Thank you for your membership to %s. Your %s membership status is: <b>%s</b>.', 'pmpro-approvals' ), get_bloginfo("name"), $current_user->membership_level->name, $approval_status) . "</p>";

		$confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account and a receipt for your initial membership invoice. A welcome email with a copy of your initial membership invoice has been sent to %s.', 'paid-memberships-pro' ), $current_user->user_email) . "</p>"; 

		return $confirmation_message;
	}
	
	/**
	 * Add email templates support for PMPro Edit Email Templates Add-on.
	 */
	public static function pmproet_templates( $pmproet_email_defaults ){

		//Add admin emails to the PMPro Edit Email Templates Add-on list.
        $pmproet_email_defaults['admin_approved'] = array(
            'subject' => __( 'A user has been approved for !!membership_level_name!!', 'pmpro-approvals'),
            'description' => __( 'Approved Email (admin)', 'pmpro-approvals')
            );

        $pmproet_email_defaults['admin_denied'] = array(
            'subject' => __( 'A user has been denied for !!membership_level_name!!', 'pmpro-approvals'),
            'description' => __( 'Denied Email (admin)', 'pmpro-approvals')
            );

        $pmproet_email_defaults['admin_notification_approved'] = array(
            'subject' => __( 'A user requires approval', 'pmpro-approvals'),
            'description' => __( 'Requires Approval (admin)', 'pmpro-approvals')
            );

        //Add user emails to the PMPro Edit Email Templates Add-on list.
        $pmproet_email_defaults['application_approved'] = array(
            'subject' => __( 'Your membership to !!sitename!! has been approved.', 'pmpro-approvals'),
            'description' => __( 'Approved Email', 'pmpro-approvals')
            );

        $pmproet_email_defaults['application_denied'] = array(
            'subject' => __( 'Your membership to !!sitename!! has been denied.', 'pmpro-approvals'),
            'description' => __( 'Denied Email', 'pmpro-approvals')
            );


        return $pmproet_email_defaults;
    }



    //Approve members from edit profile in WordPress.
    public static function show_user_profile_status( $user ){

		//get some info about the user's level
		if(isset($_REQUEST['membership_level'])) {
			$level_id = intval($_REQUEST['membership_level']);
			$level = pmpro_getLevel($level_id);
		}
		else {				
			$level = pmpro_getMembershipLevelForUser($user->ID);
			if(!empty($level))
				$level_id = $level->id;
			else
				$level_id = NULL;
		}			
	
		//process any approve/deny/reset click
		if(current_user_can('pmpro_approvals')) {
			if(!empty($_REQUEST['approve'])) {
				PMPro_Approvals::approveMember(intval($_REQUEST['approve']), $level_id);		
			}
			elseif(!empty($_REQUEST['deny']))
			{
				PMPro_Approvals::denyMember(intval($_REQUEST['deny']), $level_id);
			}
			elseif(!empty($_REQUEST['unapprove']))
			{
				PMPro_Approvals::resetMember(intval($_REQUEST['unapprove']), $level_id);
			}
		}
		
		//show info
		?>
		<table id="pmpro_approvals_status_table" class="form-table">
			<tr>
				<th><?php _e('Approval Status', 'pmpro-approvals');?></th>
				<td>
					<span id="pmpro_approvals_status_text">
						<?php echo PMPro_Approvals::getUserApprovalStatus( $user->ID, NULL, false ); ?>
					</span>
					<?php if(current_user_can('pmpro_approvals')) { ?>
					<span id="pmpro_approvals_reset_link" <?php if(PMPro_Approvals::isPending($user->ID, $level_id)) { ?>style="display: none;"<?php } ?>>
						[<a href="javascript:askfirst('Are you sure you want to reset approval for <?php echo $user->user_login;?>?', '?&user_id=<?php echo $user->ID; ?>&unapprove=<?php echo $user->ID;?>');">X</a>]
					</span>
					<span id="pmpro_approvals_approve_deny_links" <?php if(!PMPro_Approvals::isPending($user->ID, $level_id)) { ?>style="display: none;"<?php } ?>>
						<a href="?user_id=<?php echo $user->ID ?>&approve=<?php echo $user->ID;?>">Approve</a> |
						<a href="?user_id=<?php echo $user->ID ?>&deny=<?php echo $user->ID;?>">Deny</a>
					</span>
					<?php } ?>
				</td>
			</tr>
		</table>
		<script>
			var pmpro_approval_levels = <?php echo json_encode(PMPro_Approvals::getApprovalLevels());?>;
			var pmpro_approval_user_status_per_level = <?php echo json_encode(PMPro_Approvals::getUserApprovalStatuses($user->ID, true));?>;
			var pmpro_approval_user_status_full_per_level = <?php echo json_encode(PMPro_Approvals::getUserApprovalStatuses($user->ID));?>;
			
			function pmpro_approval_updateApprovalStatus() {
				//get the level from the dropdown
				var olevel = <?php echo json_encode($level_id); ?>;
				var level = jQuery('[name=membership_level]').val();
				
				//no level field, default to the user's level id
				if(typeof(level) === 'undefined')
					level = olevel;
				
				//if no level, hide it
				if(level == '') {
					//no level, so hide everything
					jQuery('#pmpro_approvals_status_table').hide();
				} else if(pmpro_approval_levels.indexOf(parseInt(level)) < 0) {
					//show the field, but hide the actions
					jQuery('#pmpro_approvals_reset_link').hide();
					jQuery('#pmpro_approvals_approve_deny_links').hide();
					
					jQuery('#pmpro_approvals_status_text').html(<?php echo json_encode(__('The chosen level does not require approval.', 'pmpro-approvals'));?>);
					
					jQuery('#pmpro_approvals_status_table').show();
				} else {
					//show the status and action links
					jQuery('#pmpro_approvals_status_text').html(pmpro_approval_user_status_full_per_level[level]);
					jQuery('#pmpro_approvals_status_table').show();
					
					if(level == olevel) {
						if(pmpro_approval_user_status_per_level[level] == 'pending') {
							jQuery('#pmpro_approvals_reset_link').hide();
							jQuery('#pmpro_approvals_approve_deny_links').show();
						} else {
							jQuery('#pmpro_approvals_reset_link').show();
							jQuery('#pmpro_approvals_approve_deny_links').hide();
						}
					} else {
						jQuery('#pmpro_approvals_reset_link').hide();
						jQuery('#pmpro_approvals_approve_deny_links').hide();
					}
				}				
			}
			
			//update approval status when the membership level select changes
			jQuery('[name=membership_level]').change(function(){pmpro_approval_updateApprovalStatus();});
			
			//call this once on load just in case
			pmpro_approval_updateApprovalStatus();
		</script>
		<?php
    }
  



} // end class

PMPro_Approvals::get_instance();
