<?php
/*
Plugin Name: Paid Memberships Pro - Approvals Add On
Plugin URI: http://www.paidmembershipspro.com/
Description: Grants administrators the ability to approve/deny memberships after signup.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
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
  		add_action( 'admin_bar_menu', array( 'PMPro_Approvals', 'admin_bar_menu' ) );
      	add_action( 'admin_init', array( 'PMPro_Approvals', 'admin_init' ) );

  		//add settings to the edit membership level page
  		/*
  			Add settings to edit level page: (see pmpro-shipping)
  			* add_action pmpro_membership_level_after_other_settings
  			* add_action pmpro_save_membership_level
  		*/
	    //load checkbox in membership level edit page for users to select.
	    add_action( 'pmpro_membership_level_after_other_settings', array( 'PMPro_Approvals', 'membership_level_after_other_settings' ) );


		//Add code for filtering checkouts, confirmation, and content filters
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
		add_submenu_page( 'pmpro-membershiplevels', __( 'Approvals', 'pmpro' ), __( 'Approvals', 'pmpro' ), 'pmpro_approvals', 'pmpro-approvals', array( 'PMPro_Approvals', 'admin_page_approvals' ) );
    }

	/**
	 * Create 'Approvals' link under the admin bar link 'Memberships'.
	 * Fires during the admin_bar_menu action.
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
  			'title' => 'Approvals',
  			'href'  => '#',
  			'parent'=>'paid-memberships-pro'
  		));
	}

	/**
	 * Load the Approvals admin page.
	 */
	public static function admin_page_approvals() {
		require_once( dirname(__FILE__) . '/adminpages/approvals.php' );
	}

	/**
	 * Get options for level.
	 */
	public static function getOptions($level_id = NULL) {
		$options = get_option('pmproapp_options', array());
		if(!empty($level_id)) {
			if(!empty($options[$level_id]))
				return $options;
			else
				return false;
		} else {
			return $options;
		}
	}

	/**
	* Load check box to make level require membership.
	*/
	public static function membership_level_after_other_settings(){
		$level_id = $_REQUEST['edit'];
		if($level_id > 0)
			$options = PMPro_Approvals::getOptions($level_id);
		else
			$options = false;
		
		?>
		<h3 class="topborder"><?php _e('Approval Settings', 'pmproapp') ?></h3>
		<table>
		<tbody class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="requires_approval"><?php _e('Requires Approval:', 'pmproapp');?></label></th>
				<td>
					<input type="checkbox" id="requires_approval" name="requires_approval" value="1" <?php checked($options['requires_approval'], 1);?> />
					<label for="requires_approval"><?php _e('Check this if membership requires approval before users are assigned to this membership level.', 'pmproapp');?></label>
				</td>
			</tr>
		</tbody>
		</table>
		<?php
	}

	/**
	 * Approve a member
	 */
	public function approveMember( $user_id, $membership_id ) {
		//update user meta to save timestamp and user who approved

		//update statuses/etc

    	//send email
	}

	/**
	 * Deny a member
	 */
	public function denyMember( $user_id, $membership_id ) {
		//update user meta to save timestamp and user who denied

		//update statuses/etc

		//send emails
 
	}

	/**
	 * Reset a member to pending approval status
	 */
	public function resetMember( $user_id, $membership_id ) {
		// update user back to "pending" status.

    	//send email that user has been Reset

	}

} // end class

PMPro_Approvals::get_instance();
