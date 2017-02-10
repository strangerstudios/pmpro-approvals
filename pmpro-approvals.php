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

  		//add admin menu items to 'Memberships' in WP dashboard and admin bar
  		add_action( 'admin_menu', array( 'PMPro_Approvals', 'admin_menu' ) );
  		add_action( 'admin_bar_menu', array( 'PMPro_Approvals', 'admin_bar_menu' ), 1000 );
      	add_action( 'admin_init', array( 'PMPro_Approvals', 'admin_init' ) );

		//set status when user's checkout
		add_action( 'pmpro_after_checkout', array( 'PMPro_Approvals', 'pmpro_after_checkout' ) );
		add_action( 'pmpro_after_change_membership_level', array( 'PMPro_Approvals', 'pmpro_after_change_membership_level' ) );
		
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
		add_filter( 'pmpro_non_member_text_filter', array( $this, 'change_message_protected_content' ) );
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
		//code to go here if need
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
	 * Update membership status to pending_approval if the level requires approval
	 * Fires on pmpro_after_checkout
	 */
	public static function pmpro_after_checkout( $user_id ) {
		//get their membership level
			

			//check if they've already been approved or not
			$user_approved = get_user_meta( $user_id, 'pmpro_approval', true);
			
			if( $user_approved == 'approved' ){
				return false;
			}else{
				//get options
				$options = PMPro_Approvals::getOptions();
			}
			
			
			
		
			//if not, query DB to change status to pending_approval

	}
	
	/**
	 * Filter hasMembershipLevel if user is not approved
	 * Fires on pmpro_has_membership_access_filter
	 */
	public static function pmpro_has_membership_access_filter( $access, $post, $user, $levels ) {
		
		$user_approved = get_user_meta( $user->id, 'pmpro_approval', true );
		//return false if already false
		$levels = PMPro_Approvals::getApprovalLevels();
		//check if user's level requires approval
		foreach( $levels as $level => $level_id ){
			if( $level_id == $user->membership_level->id ){	
				if( empty( $user_approved ) || $user_approved['status'] != 'approved'){
					$access = false;
				}
			}
		}
		
		return $access;
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

			//use PMPro_Approvals::getApprovalLevels() ... AND mu.membership_id IN($levels)
			if($l)
				$sqlQuery .= " AND mu.membership_id = '" . $l . "' ";											
			
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
	public static function approveMember( $user_id, $membership_id ) {
		global $current_user, $msg, $msgt;
		
		//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmproapp');
			
			return false;
		}
		
		//update user meta to save timestamp and user who approved
		update_user_meta($user_id, "pmpro_approval", array("status"=>"approved", "timestamp"=>time(), "who" => $current_user->ID, "approver"=>$current_user->user_login));						
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was approved.", 'pmproapp');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membeship at %s has been approved.", 'pmproapp'), get_bloginfo('name'));
		$approval_email->template = "application_approved";
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url());
		$approval_email->sendEmail();
		
		return true;
	}

	/**
	 * Deny a member
	 */
	public static function denyMember( $user_id, $membership_id ) {
		global $current_user, $msg, $msgt;

		//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmproapp');
			
			return false;
		}
		
		//update user meta to save timestamp and user who approved
		update_user_meta($user_id, "pmpro_approval", array("status"=>"denied", "timestamp"=>time(), "who" => $current_user->ID, "approver"=>$current_user->user_login));						
		
		//update statuses/etc
		$msg = 1;
		$msgt = __("Member was denied.", 'pmproapp');
					
		//send email
		$a_user = get_userdata($user_id);
		$approval_email = new PMProEmail();
		$approval_email->email = $a_user->user_email;
		$approval_email->subject = sprintf(__("Your membeship at %s has been denied.", 'pmproapp'), get_bloginfo('name'));
		$approval_email->template = "application_denied";
		$approval_email->data = array("display_name" => $a_user->display_name, "user_email" => $a_user->user_email, "login_link" => wp_login_url()); //Update this?
		$approval_email->sendEmail();
		
		return true;
 
	}

	/**
	 * Reset a member to pending approval status
	 */
	public static function resetMember( $user_id, $membership_id ) {
		global $current_user, $msg, $msgt;
		
    	//make sure they have permission
		if(!current_user_can("manage_options") && !current_user_can("pmpro_approvals")) {
			$msg = -1;
			$msgt = __("You do not have permission to perform approvals.", 'pmproapp');
			
			return false;
		}
		
		$d_user_id = intval($_REQUEST['unapprove']);
						
		delete_user_meta($d_user_id, "pmpro_approval");
			
		$msg = 1;
		$msgt = __("Member has been unapproved.", 'pmproapp');	
		
		return true;

	}

	/**
	*
	**/
	public static function change_message_protected_content( $text ){

		global $current_user, $has_access;

		$user_approved = get_user_meta( $current_user->id, 'pmpro_approval', true );	

		if( $user_approved != 'approved' ){
			$text = __( 'Your membership requires approval in order to view this content.', 'pmpro-approvals' );
		}

		return $text;
	}

} // end class

PMPro_Approvals::get_instance();


//add_action('wp_footer', 'pmpro_after_checkout' );
