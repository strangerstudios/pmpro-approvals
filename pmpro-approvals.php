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

		//set status when user's checkout
		//add_action( 'pmpro_after_checkout', array( 'PMPro_Approvals', 'pmpro_after_checkout' ), 10, 2 );
		//add_action( 'pmpro_after_change_membership_level', array( 'PMPro_Approvals', 'pmpro_after_change_membership_level' ) );
		
		//filter membership and content access
		//add_filter( 'pmpro_has_membership_level', array( 'PMPro_Approvals', 'pmpro_has_membership_level' ), 10, 3 );
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
		add_filter( 'pmpro_non_member_text_filter', array( 'PMPro_Approvals', 'change_message_protected_content' ) );
    }

    /**
    * Run code on admin init
    */
    public static function admin_init(){
    	//TODO: Add Approver role (maybe in activation/deactivation)
		
        //get role of administrator
        $role = get_role( 'administrator' );
        //add custom capability
        $role->add_cap( 'pmpro_approvals' );
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
	 * Filter hasMembershipLevel if user is not approved
	 * Fires on pmpro_has_membership_access_filter
	 */
	public static function pmpro_has_membership_access_filter( $access, $post, $user, $levels ) {
		
		//if we don't have access now, we still won't
		if(!$access)
			return $access;
		
		//now we need to check if the user is approved for ANY of the $levels
		
		return $access;
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
	 * Check if a user is approved.
	 */
	public static function isApproved($user_id = NULL, $level_id = NULL) {	
		//get approval for this user/level
		$user_approval = PMPro_Approvals::getUserApproval($user_id, $level_id);
		
		//if no array, return false
		if(empty($user_approval) || !is_array($user_approval))
			return false;				
		
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
	public static function getApprovals( $l = false, $s = '', $sortby = 'user_registered', $sortorder = 'ASC', $pn = 1, $limit = 15 ) {		
		global $wpdb;
		
		$end = $pn * $limit;
		$start = $end - $limit;				
		
		if($s)
		{
			$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id ";
			
			if($sortby == "pmpro_approval")
				$sqlQuery .= " LEFT JOIN $wpdb->usermeta um2 ON um2.user_id = u.ID AND um2.meta_key = 'pmpro_approval' ";
			
			$sqlQuery .= "WHERE mu.status = 'active' AND mu.membership_id > 0 AND (u.user_login LIKE '%$s%' OR u.user_email LIKE '%$s%' OR um.meta_value LIKE '%$s%') ";
			
			if($l)
				$sqlQuery .= " AND mu.membership_id = '" . $l . "' ";											
			else
				$sqlQuery .= " AND mu.membership_id IN(" . implode(',', PMPro_Approvals::getApprovalLevels()) . ") ";
			
			$sqlQuery .= "GROUP BY u.ID ";
			
			if($sortby == "pmpro_approval")
				$sqlQuery .= "ORDER BY (um2.meta_value IS NULL) $sortorder ";		
			else
				$sqlQuery .= "ORDER BY $sortby $sortorder ";
				
			$sqlQuery .= "LIMIT $start, $limit";				
		}
		else
		{
			$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id";
			
			if($sortby == "pmpro_approval")
				$sqlQuery .= " LEFT JOIN $wpdb->usermeta um ON um.user_id = u.ID AND um.meta_key = 'pmpro_approval' ";
						
			$sqlQuery .= " WHERE mu.membership_id > 0  AND mu.status = 'active' ";
			if($l)
				$sqlQuery .= " AND mu.membership_id = '" . $l . "' ";								
			else
				$sqlQuery .= " AND mu.membership_id IN(" . implode(',', PMPro_Approvals::getApprovalLevels()) . ") ";
			
			if($sortby == "pmpro_approval")
				$sqlQuery .= "ORDER BY (um.meta_value IS NULL) $sortorder ";		
			else
				$sqlQuery .= "ORDER BY $sortby $sortorder ";
				
			$sqlQuery .= "LIMIT $start, $limit";			
		}
						
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
		update_user_meta($user_id, "pmpro_approval_" . $level_id, array("status"=>"approved", "timestamp"=>time(), "who" => $current_user->ID, "approver"=>$current_user->user_login));						
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was approved.", 'pmpro-approvals');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membeship at %s has been approved.", 'pmpro-approvals'), get_bloginfo('name'));
		$approval_email->template = "application_approved";
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url());
		$approval_email->sendEmail();
		
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
		update_user_meta($user_id, "pmpro_approval_" . $level_id, array("status"=>"denied", "timestamp"=>time(), "who" => $current_user->ID, "approver"=>$current_user->user_login));						
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was denied.", 'pmpro-approvals');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membeship at %s has been denied.", 'pmpro-approvals'), get_bloginfo('name'));
		$approval_email->template = "application_denied";
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url()); //Update this?
		$approval_email->sendEmail();
		
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
		
		delete_user_meta($user_id, "pmpro_approval_" . $level_id);
			
		$msg = 1;
		$msgt = __("Approval reset.", 'pmpro-approvals');	
		
		return true;

	}

	/**
	*
	**/
	public static function change_message_protected_content( $text ){

		global $current_user, $has_access;

		if(PMPro_Approvals::isApproved()) {
			$text = __( 'Your membership requires approval in order to view this content.', 'pmpro-approvals' );
		}

		return $text;
	}

} // end class

PMPro_Approvals::get_instance();


//add_action('wp_footer', 'pmpro_after_checkout' );
