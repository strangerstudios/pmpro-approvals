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
		
		//add settings to the edit membership level page
		/*
			Add settings to edit level page: (see pmpro-shipping)
			* add_action pmpro_membership_level_after_other_settings
			* add_action pmpro_save_membership_level			
		*/
		
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
	 * Create the submenu item 'Approvals' under the 'Memberships' link in WP dashboard.
	 * Fires during the "admin_menu" action.
	 */
    public static function admin_menu(){		
		add_submenu_page('pmpro-membershiplevels', __('Approvals', 'pmpro'), __('Approvals', 'pmpro'), 'administrator', 'pmpro-approvals', array('PMPro_Approvals', 'admin_page_approvals'));
    }

	/**
	 * Create 'Approvals' link under the admin bar link 'Memberships'.
	 * Fires during the admin_bar_menu action.
	 */
    public static function admin_bar_menu(){
		global $wp_admin_bar;
		
		//check capabilities (TODO: Define a new capability (pmpro_approvals) for managing approvals.)
		if ( !is_super_admin() || !is_admin_bar_showing() )
			return;

		//add the link
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
		require_once(dirname(__FILE__) . '/adminpages/approvals.php');
	}
	
	/**
	 * Approve a member
	 */
	public function approveMember($user_id, $membership_id) {
		//update user meta to save timestamp and user who approved
		
		//update statues/etc
		
		//send emails
	}
	
	/**
	 * Deny a member
	 */
	public function denyMember($user_id, $membership_id) {
		//update user meta to save timestamp and user who denied
		
		//update statues/etc
		
		//send emails
	}
	
	/**
	 * Reset a member to pending approval status
	 */
	public function resetMember($user_id, $membership_id) {
		// ???
	}

} // end class

PMPro_Approvals::get_instance();