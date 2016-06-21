<?php
	//only admins can get this
	if ( ! function_exists( "current_user_can" ) || ( ! current_user_can( "manage_options" ) && ! current_user_can( "pmpro_approvals" ) ) ) {
		die( __( "You do not have permissions to perform this action.", "pmproapp" ) );
	}

	require_once( PMPRO_DIR . "/adminpages/admin_header.php" );
?>
<h2>Approvals</h2>
<!-- This would be the list of outstanding members? -->
<p>Content will go here.</p>
<?php
	require_once( PMPRO_DIR . "/adminpages/admin_footer.php" );
?>
