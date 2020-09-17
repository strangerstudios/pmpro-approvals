<?php

class PMPro_Approvals_Check {

	/**
	 * The gateway ID for Pay By Check.
	 *
	 * @var string
	 */
	const PAY_BY_CHECK = 'check';

	/**
	 * The status value for a pending order.
	 *
	 * @var string
	 */
	const PENDING_STATUS = 'pending';

	/**
	 * The status value of a successful order
	 *
	 * @var string
	 */
	const SUCCESS_STATUS = 'success';

	/**
	 * Singleton instance.
	 *
	 * @var \PMPro_Approvals_Check
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  PMPro_Approvals_Check A single instance of this class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PMPro_Approvals_Check();
		}

		return self::$instance;
	}

	/**
	 * Create a new class instance.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'init', array( static::class, 'init' ) );
	}

	/**
	 * Initialise actions and filters
	 *
	 * @return void
	 */
	public static function init() {
		// bail if PMPro is not loaded
		if ( ! defined( 'PMPRO_VERSION' ) ) {
			return;
		}

		add_action( 'pmpro_approvals_after_approve_member', array( static::class, 'pmpro_approvals_after_approve_member' ), 10, 2 );
		add_action( 'pmpro_update_order', array( static::class, 'pmpro_update_order' ), 10, 2 );
	}

	/**
	 * Handle pmpro_approvals_after_approve_member hook
	 *
	 * @param  int $user_id  User ID of the member who was approved
	 * @param  int $level_id Level ID for the member that was approved
	 * @return void
	 */
	public static function pmpro_approvals_after_approve_member($user_id, $level_id) {
		// get the user's most recent order for the level and gateway
		$lastorder = new MemberOrder();
		$lastorder->getLastMemberOrder( $user_id, null, $level_id, self::PAY_BY_CHECK );

		if ( empty( $lastorder ) ) {
			return;
		}

		// ignore if the order status is anything besides pending
		if ( $lastorder->status !== self::PENDING_STATUS ) {
			return;
		}

		// mark the order as successful and save
		$lastorder->status = self::SUCCESS_STATUS;
		$lastorder->saveOrder();
	}

	/**
	 * Handle pmpro_update_order hook
	 *
	 * @param  \MemberOrder $order Order that is about to be updated
	 * @return \MemberOrder
	 */
	public static function pmpro_update_order($order) {
		$previous_order = new MemberOrder();
		$previous_order->getMemberOrderByID( $order->id );

		// bail if we are not transitioning from pending to success on a check order
		if ( ! ( $previous_order->status === 'pending' && $order->status === 'success' && $order->gateway === 'check' ) ) {
			return $order;
		}

		$membership_level = $previous_order->getMembershipLevel();
		// bail if the user has no pending approval for the membership level
		if ( ! PMPro_Approvals::isPending( $order->user_id, $membership_level->id ) ) {
			return $order;
		}

		// temporarily remove hook to prevent a race condition
		remove_action( 'pmpro_approvals_after_approve_member', array( static::class, 'pmpro_approvals_after_approve_member' ), 10, 2 );
		// approve the member
		PMPro_Approvals::approveMember( $order->user_id, $membership_level->id );
		// restore hook
		add_action( 'pmpro_approvals_after_approve_member', array( static::class, 'pmpro_approvals_after_approve_member' ), 10, 2 );
		return $order;
	}

}

PMPro_Approvals_Check::get_instance();
